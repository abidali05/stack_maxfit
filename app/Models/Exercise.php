<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $guarded = [];

    public function exercise_category()
    {
        return $this->belongsTo(ExerciseCategory::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
}
