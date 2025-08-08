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

    public function competitionUsers()
    {
        return $this->hasMany(CompetitionUser::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class, 'genz', 'genz');
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
