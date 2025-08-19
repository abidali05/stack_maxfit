<?php

namespace App\Http\Controllers\Coach;

use Illuminate\Http\Request;
use App\Models\CompetitionUser;
use App\Models\CompetitionDetail;
use App\Models\CompetitionResult;
use App\Http\Controllers\Controller;
use App\Models\CompetitionUserTotal;
use Illuminate\Support\Facades\Auth;

class ManageCompetitionController extends Controller
{
    public function getCompetitionDetail()
    {
        $competitionDetails = CompetitionDetail::where('coach_id', Auth::guard('coach')->user()->id)->get();
        return view('coach.competition-list', compact('competitionDetails'));
    }

    public function getCompetitionDetailUser($id)
    {
        $competition = CompetitionDetail::with('competitionUsers.user')->findOrFail($id);

        return view('coach.competition-list-user', compact('competition'));
    }

    public function getCompetitionDetailUserUpdate($id)
    {
        $competitionUser = CompetitionUser::with(['user', 'competitionDetail.competition'])->findOrFail($id);

        // Get all exercises for this competition
        $competition = $competitionUser->competitionDetail->competition;
        $exercises = $competition->exercises; // via competition_exercises

        // Get existing scores
        $results = CompetitionResult::where('competition_user_id', $competitionUser->id)->get()->keyBy('exercise_id');

        return view('coach.competition-users-edit', compact('competitionUser', 'exercises', 'results'));
    }

    public function getCompetitionResultUpdate(Request $request,$id)
    {
        $competitionUser = CompetitionUser::findOrFail($id);

        $scores = $request->input('scores'); // array: exercise_id => score

        $totalScore = 0;

        foreach ($scores as $exerciseId => $score) {
            CompetitionResult::updateOrCreate(
                [
                    'competition_user_id' => $competitionUser->id,
                    'exercise_id' => $exerciseId
                ],
                ['score' => $score]
            );

            $totalScore += floatval($score);
        }

        // Save or update total score
        CompetitionUserTotal::updateOrCreate(
            ['competition_user_id' => $competitionUser->id],
            ['total_score' => $totalScore]
        );

        return redirect()->back()->with('success', 'Scores updated!');
    }
}
