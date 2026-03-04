<?php

namespace App\Http\Controllers;

use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends BaseController
{
    /**
     * Store a newly created schedule with conflict detection.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => ['required', 'exists:courses,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'lecturer_id' => ['required', 'exists:lecturers,id'],
            'day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();
            $day = $validated['day'];
            $startTime = $validated['start_time'];
            $endTime = $validated['end_time'];
            $classroomId = $validated['classroom_id'];
            $lecturerId = $validated['lecturer_id'];

            // --- 1. Conflict Detector: Room Availability ---
            // Check if the classroom is already occupied on the same day overlapping the time.
            $roomConflict = Schedule::where('classroom_id', $classroomId)
                ->where('day', $day)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })->exists();

            if ($roomConflict) {
                return $this->sendError('Validation Error', ['classroom_id' => ['The classroom is already occupied during this time.']], 422);
            }

            // --- 2. Conflict Detector: Lecturer Availability ---
            // Check if the lecturer is already teaching another class at exactly the same day/time overlap.
            $lecturerConflict = Schedule::where('lecturer_id', $lecturerId)
                ->where('day', $day)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })->exists();

            if ($lecturerConflict) {
                return $this->sendError('Validation Error', ['lecturer_id' => ['The lecturer is already scheduled to teach during this time.']], 422);
            }

            // If no conflicts, create Schedule
            $schedule = Schedule::create($validated);

            DB::commit();

            $schedule->load(['course', 'classroom', 'lecturer']);

            return $this->sendResponse(new ScheduleResource($schedule), 'Schedule created successfully.', 201);
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
