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
}
