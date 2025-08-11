<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionResult extends Model
{
    protected $guarded = [];

    public function competitionUser()
    {
        return $this->belongsTo(CompetitionUser::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
