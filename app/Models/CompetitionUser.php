<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionUser extends Model
{
    protected $guarded = [];

    public function competitionResult()
    {
        return $this->hasOne(CompetitionResult::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
