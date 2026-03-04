<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CourseController extends BaseController
{
    /**
     * Display a listing of courses, filterable by study_program_id via curriculum.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::query()->with('prerequisite');

        // Optional filter by Study Program ID
        if ($request->has('study_program_id')) {
            $query->whereHas('curriculums', function ($q) use ($request) {
                $q->where('study_program_id', $request->query('study_program_id'));
            });
        }

        $courses = $query->paginate(15);

        return $this->sendResponse(
            [
                'courses' => CourseResource::collection($courses),
                'meta' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'total' => $courses->total(),
                ]
            ],
            'Courses retrieved successfully.'
        );
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'unique:courses'],
            'name' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1'],
            'semester_type' => ['required', 'in:Odd,Even'],
            'prerequisite_course_id' => ['nullable', 'exists:courses,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            DB::beginTransaction();

            $course = Course::create($validator->validated());

            DB::commit();

            return $this->sendResponse(new CourseResource($course), 'Course created successfully.', 201);
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
     * Update the specified course.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'unique:courses,code,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1'],
            'semester_type' => ['required', 'in:Odd,Even'],
            'prerequisite_course_id' => ['nullable', 'exists:courses,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            DB::beginTransaction();

            $course = Course::findOrFail($id);
            $course->update($validator->validated());

            DB::commit();

            return $this->sendResponse(new CourseResource($course), 'Course updated successfully.');
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
     * Remove the specified course.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $course = Course::findOrFail($id);
            $course->delete(); // Soft delete applied via Model

            DB::commit();

            return $this->sendResponse([], 'Course deleted successfully.');
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
