<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseScoreResource;
use App\Http\Resources\GpaSummaryResource;
use App\Http\Resources\GradeSettingResource;
use App\Models\CourseScore;
use App\Models\GpaSummary;
use App\Models\GradeSetting;
use App\Models\KrsPeriod;
use App\Services\GradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ScoreController extends BaseController
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Get all active grading scale configurations.
     */
    public function getSettings(): JsonResponse
    {
        $settings = GradeSetting::orderBy('weight', 'desc')->get();
        return $this->sendResponse(GradeSettingResource::collection($settings), 'Grade Settings retrieved successfully.');
    }

    /**
     * Lecturer batch input for a specific class schedule.
     */
    public function inputBatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => ['required', 'exists:schedules,id'],

            // Allow dynamic percentage weights from frontend/lecturer (must sum to 100 later optionally)
            'weight_attendance' => ['required', 'numeric', 'min:0', 'max:100'],
            'weight_assignment' => ['required', 'numeric', 'min:0', 'max:100'],
            'weight_uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'weight_uas' => ['required', 'numeric', 'min:0', 'max:100'],

            // Array of students and their scores
            'scores' => ['required', 'array'],
            'scores.*.student_id' => ['required', 'exists:students,id'],
            'scores.*.score_attendance' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.score_assignment' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.score_uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.score_uas' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

            // Verify active period (or accept schedule directly if grades logic bypasses strict temporal locks)
            $activePeriod = KrsPeriod::where('is_active', true)->first();
            if (!$activePeriod) {
                return $this->sendError('Validation Error', ['period' => ['No active KRS Period. Grades usually bound to active semesters.']], 422);
            }

            $scheduleId = $validated['schedule_id'];
            $wAtt = $validated['weight_attendance'] / 100;
            $wAss = $validated['weight_assignment'] / 100;
            $wUts = $validated['weight_uts'] / 100;
            $wUas = $validated['weight_uas'] / 100;

            if (round($wAtt + $wAss + $wUts + $wUas, 2) !== 1.00) {
                return $this->sendError('Validation Error', ['weights' => ['Total percentage weights must sum to exactly 100.']], 422);
            }

            $results = collect($validated['scores'])->map(function ($scoreData) use ($scheduleId, $activePeriod, $wAtt, $wAss, $wUts, $wUas) {
                // Calculate Final Numeric Score based on weighted metrics
                $finalNumeric = ($scoreData['score_attendance'] * $wAtt) +
                    ($scoreData['score_assignment'] * $wAss) +
                    ($scoreData['score_uts'] * $wUts) +
                    ($scoreData['score_uas'] * $wUas);

                // Fetch scale from Service
                $gradeConfig = $this->gradingService->calculateScoreLetterAndWeight($finalNumeric);

                // If grading scale isn't seeded/setup, fallback explicitly
                $finalLetter = $gradeConfig ? $gradeConfig->grade_letter : 'E';
                $finalWeight = $gradeConfig ? $gradeConfig->weight : 0.0;

                // Create or Update the Course Score
                $courseScore = CourseScore::updateOrCreate(
                    [
                        'student_id' => $scoreData['student_id'],
                        'schedule_id' => $scheduleId,
                        // Ensure we update across exact periods
                        'krs_period_id' => $activePeriod->id
                    ],
                    [
                        'score_attendance' => $scoreData['score_attendance'],
                        'score_assignment' => $scoreData['score_assignment'],
                        'score_uts' => $scoreData['score_uts'],
                        'score_uas' => $scoreData['score_uas'],
                        'final_score_numeric' => $finalNumeric,
                        'final_score_letter' => $finalLetter,
                        'final_weight' => $finalWeight,
                    ]
                );

                // Re-calculate the student's IPS and IPK for this period
                $this->gradingService->calculateIPS($scoreData['student_id'], $activePeriod->id);

                return clone $courseScore;
            });

            DB::commit();

            return $this->sendResponse(['total_processed' => $results->count()], 'Batch grading successfully processed.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
            return $this->sendError('Server Error', [], 500);
        }
    }

    /**
     * Retrieve KHS (IPS/Semester results) for a specific student and period.
     */
    public function getKhs(Request $request, string $studentId, string $krsPeriodId): JsonResponse
    {
        $scores = CourseScore::with(['schedule.course', 'schedule.lecturer'])
            ->where('student_id', $studentId)
            ->where('krs_period_id', $krsPeriodId)
            ->get();

        $gpaSummary = GpaSummary::where('student_id', $studentId)
            ->where('krs_period_id', $krsPeriodId)
            ->first();

        return $this->sendResponse([
            'summary' => $gpaSummary ? new GpaSummaryResource($gpaSummary->load('krsPeriod')) : null,
            'details' => CourseScoreResource::collection($scores)
        ], 'KHS retrieved successfully.');
    }

    /**
     * Retrieve final Transcript (IPK) condensing distinct max-courses.
     */
    public function getTranscript(Request $request, string $studentId): JsonResponse
    {
        // 1. Fetch ALL scores historical for student
        $allScores = CourseScore::with(['schedule.course'])
            ->where('student_id', $studentId)
            ->whereNotNull('final_weight')
            ->get();

        $bestGrades = [];

        // 2. Collapse by Course ID (Max final_weight wins out)
        foreach ($allScores as $score) {
            $courseId = $score->schedule->course_id;

            if (!isset($bestGrades[$courseId])) {
                $bestGrades[$courseId] = clone $score; // clone to preserve relation safety
            } else {
                if ($score->final_weight > $bestGrades[$courseId]->final_weight) {
                    $bestGrades[$courseId] = clone $score;
                }
            }
        }

        // 3. Pull Current IPK summary representing global academic standing
        // Find latest GpaSummary assuming it handles cumulative totals
        $globalGpa = GpaSummary::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->first();

        // 4. Return
        return $this->sendResponse([
            'cumulative_summary' => $globalGpa ? new GpaSummaryResource($globalGpa) : null,
            'transcript_details' => CourseScoreResource::collection(array_values($bestGrades))
        ], 'Cumulative Transcript generated successfully.');
    }
}
