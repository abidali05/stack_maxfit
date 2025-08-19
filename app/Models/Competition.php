<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    protected $guarded = [];

    public function videos()
    {
        return $this->hasMany(CompetitionVideo::class);
    }

    public function competitionResult()
    {
        return $this->hasMany(CompetitionResult::class);
    }

    public function details()
    {
        return $this->hasMany(CompetitionDetail::class);
    }

    public function competitionDetail()
    {
        return $this->hasOne(CompetitionDetail::class, 'competition_id');
    }

    public function competitionUsers()
    {
        return $this->hasMany(CompetitionUser::class);
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'competition_exercises', 'competition_id', 'exercise_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisations::class, 'org');
    }

    public function organisationType()
    {
        return $this->belongsTo(OrganisationTypes::class, 'org_type');
    }
}
