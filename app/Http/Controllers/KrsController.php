<?php

namespace App\Http\Controllers;

use App\Http\Resources\KrsItemResource;
use App\Http\Resources\ScheduleResource;
use App\Models\KrsItem;
use App\Models\KrsPeriod;
use App\Models\Schedule;
use App\Models\StudentStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KrsController extends BaseController
{
    /**
     * Get available courses/schedules for the current active period.
     */
    public function getAvailableCourses(Request $request): JsonResponse
    {
        // 1. Find Active KrsPeriod
        $activePeriod = KrsPeriod::where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$activePeriod) {
            return $this->sendError('Not Found', ['krs_period' => ['No active KRS enrollment period found for today.']], 404);
        }

        // Ideally, we'd fetch schedules relevant to the current semester (odd/even matching).
        // For simplicity based on requirements, we fetch all schedules loaded with relations.
        $schedules = Schedule::with(['course', 'classroom', 'lecturer'])->get();

        return $this->sendResponse(ScheduleResource::collection($schedules), 'Available schedules retrieved successfully.');
    }

    /**
     * Main Engine: Enroll student into a schedule.
     */
    public function enroll(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => ['required', 'exists:schedules,id'],
            // In a real scenario, student_id comes from Auth user.
            // Requiring here explicitly to allow Admin proxy or flexible testing based on spec.
            'student_id' => ['required', 'exists:students,id']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();
            $scheduleId = $validated['schedule_id'];
            $studentId = $validated['student_id'];

            // --- Validation 1: Period Activeness ---
            $activePeriod = KrsPeriod::where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();

            if (!$activePeriod) {
                return $this->sendError('Validation Error', ['krs_period' => ['No active KRS enrollment period found for today.']], 422);
            }

            // Check if already enrolled in this exact schedule during this period
            $alreadyEnrolled = KrsItem::where('student_id', $studentId)
                ->where('krs_period_id', $activePeriod->id)
                ->where('schedule_id', $scheduleId)
                ->exists();

            if ($alreadyEnrolled) {
                return $this->sendError('Validation Error', ['schedule_id' => ['You are already enrolled in this schedule.']], 422);
            }

            // Retrieve Target Schedule with Course Info
            $targetSchedule = Schedule::with(['course', 'classroom'])->findOrFail($scheduleId);

            // --- Validation 2: Classroom Capacity Limits ---
            $enrolledCount = KrsItem::where('schedule_id', $scheduleId)->count();
            if ($enrolledCount >= $targetSchedule->classroom->capacity) {
                return $this->sendError('Validation Error', ['capacity' => ['The classroom is already full for this schedule.']], 422);
            }

            // --- Validation 3: SKS Quota Limit Check ---
            // 3a. Get Student Stat Max SKS
            $studentStat = StudentStat::firstOrCreate(
                ['student_id' => $studentId],
                ['max_sks_allowed' => 24] // Default max SKS
            );
            $maxSks = $studentStat->max_sks_allowed;

            // 3b. Sum up existing taken SKS in current period
            $existingSelectedSks = KrsItem::where('student_id', $studentId)
                ->where('krs_period_id', $activePeriod->id)
                ->with('schedule.course')
                ->get()
                ->sum(function ($item) {
                    return $item->schedule->course->sks;
                });

            $addedSks = $targetSchedule->course->sks;

            if (($existingSelectedSks + $addedSks) > $maxSks) {
                return $this->sendError('Validation Error', ['sks_quota' => ["Exceeds max SKS layout. Permitted: {$maxSks}, Attempting: " . ($existingSelectedSks + $addedSks)]], 422);
            }

            // --- Validation 4: Schedule Overlap/Timetable conflict ---
            $targetDay = $targetSchedule->day;
            $targetStartTime = $targetSchedule->start_time;
            $targetEndTime = $targetSchedule->end_time;

            $hasOverlap = KrsItem::where('student_id', $studentId)
                ->where('krs_period_id', $activePeriod->id)
                ->whereHas('schedule', function ($q) use ($targetDay, $targetStartTime, $targetEndTime) {
                    $q->where('day', $targetDay)
                        ->where(function ($subQ) use ($targetStartTime, $targetEndTime) {
                            $subQ->whereBetween('start_time', [$targetStartTime, $targetEndTime])
                                ->orWhereBetween('end_time', [$targetStartTime, $targetEndTime])
                                ->orWhere(function ($q2) use ($targetStartTime, $targetEndTime) {
                                    $q2->where('start_time', '<=', $targetStartTime)
                                        ->where('end_time', '>=', $targetEndTime);
                                });
                        });
                })->exists();

            if ($hasOverlap) {
                return $this->sendError('Validation Error', ['schedule' => ['The selected schedule overlaps with your existing registered courses.']], 422);
            }

            // --- Validation 5: Prerequisite Course Logic ---
            $prerequisiteId = $targetSchedule->course->prerequisite_course_id;
            if ($prerequisiteId) {
                // In a mature system, you check `Grades` or `Transcripts` here where status = passed.
                // Assuming we check if the student simply has an approved KrsItem for the prereq in a PAST period.
                $hasPassedPrereq = KrsItem::where('student_id', $studentId)
                    ->where('status', 'Approved') // Approved meaning completed/passed contextually
                    ->whereHas('schedule', function ($q) use ($prerequisiteId) {
                        $q->where('course_id', $prerequisiteId);
                    })->exists();

                if (!$hasPassedPrereq) {
                    return $this->sendError('Validation Error', ['prerequisite' => ['You have not met the prerequisite course requirements to take this schedule.']], 422);
                }
            }

            // Passes All Gates -> Enroll as Draft
            $krsItem = KrsItem::create([
                'student_id' => $studentId,
                'schedule_id' => $scheduleId,
                'krs_period_id' => $activePeriod->id,
                'status' => 'Draft'
            ]);

            DB::commit();

            $krsItem->load(['schedule.course', 'schedule.classroom', 'schedule.lecturer', 'krsPeriod', 'student.user']);

            return $this->sendResponse(new KrsItemResource($krsItem), 'Successfully enrolled into schedule. Waiting for advisor approval.', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->sendError('Server Error', [], 500);
        }
    }

    /**
     * Approve KRS for a specific student for the current active period.
     */
    public function approve(Request $request, string $studentId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $activePeriod = KrsPeriod::where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();

            if (!$activePeriod) {
                return $this->sendError('Validation Error', ['krs_period' => ['No active KRS enrollment period found. Can only approve active period KRS.']], 422);
            }

            $affectedRows = KrsItem::where('student_id', $studentId)
                ->where('krs_period_id', $activePeriod->id)
                ->where('status', '!=', 'Approved')
                ->update(['status' => 'Approved']);

            if ($affectedRows === 0) {
                return $this->sendResponse([], 'No pending KRS items found to approve for this student.');
            }

            DB::commit();

            return $this->sendResponse([], "Successfully approved {$affectedRows} KRS items for the student.");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->sendError('Server Error', [], 500);
        }
    }
}
