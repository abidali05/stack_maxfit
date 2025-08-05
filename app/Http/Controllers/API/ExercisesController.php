<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseCategory;

class ExercisesController extends Controller
{
    public function __invoke()
    {
        $exercises = Exercise::get();
        return $this->success($exercises, 'Exercises fetched successfully', 200);
    }

    public function getCategory()
    {
        $category = ExerciseCategory::latest()->get();
        return $this->success($category, 'Category fetched successfully', 200);
    }

    public function getCategoryExercises($id)
    {
        $category = ExerciseCategory::findOrFail($id);
        $exercises = Exercise::where('exercise_category_id', $id)->latest()->get();

        return $this->success([
            'category' => $category,
            'exercises' => $exercises
        ], 'Exercises fetched successfully', 200);
    }
}
