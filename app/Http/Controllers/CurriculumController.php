<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurriculumRequest;
use App\Http\Resources\CurriculumResource;
use App\Models\Course;
use App\Models\Curriculum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CurriculumController extends BaseController
{
    /**
     * Store a newly created curriculum and attach courses.
     */
    public function store(StoreCurriculumRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $curriculum = Curriculum::create([
                'study_program_id' => $validated['study_program_id'],
                'name' => $validated['name'],
                'year' => $validated['year'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Sync/Attach courses to this curriculum if provided
            if (!empty($validated['course_ids'])) {
                $courses = Course::whereIn('id', $validated['course_ids'])->get();

                // Optional advanced logic: Verification of Course Prerequisite inside curriculum assignment
                // (Assuming simple attach here, advanced logic can be expanded based on exact spec)

                $curriculum->courses()->attach($courses->pluck('id'));
            }

            DB::commit();

            $curriculum->load(['studyProgram', 'courses.prerequisite']);

            return $this->sendResponse(new CurriculumResource($curriculum), 'Curriculum created successfully.', 201);
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
