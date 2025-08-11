<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Exercise;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Models\CompetitionUser;
use App\Models\RulesOfCounting;
use App\Models\CompetitionVideo;
use App\Models\CompetitionAppeal;
use App\Models\CompetitionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CompetitionController extends Controller
{
    public function getCompetition()
    {
        $authId = Auth::id();

        $competitions = Competition::with(['details', 'videos', 'exercises'])
            ->where('status', 'active')
            ->get()
            ->map(function ($competition) use ($authId) {
                // Find the status via competition_details → competition_users
                $competition->status_type = DB::table('competition_users')
                    ->join('competition_details', 'competition_users.competition_detail_id', '=', 'competition_details.id')
                    ->where('competition_details.competition_id', $competition->id)
                    ->where('competition_users.user_id', $authId)
                    ->value('competition_users.status');

                return $competition;
            });

        return $this->success([
            'competitions' => $competitions,
        ], 'Competitions fetched successfully', 200);
    }


    public function getCompetitionStatus(Request $request)
    {
        $authId = Auth::id();

        // Map numeric status to text
        $statusText = $request->status == 1 ? 'accepted' : 'rejected';

        $competitions = Competition::whereHas('details.competitionUsers', function ($query) use ($authId, $statusText) {
            $query->where('user_id', $authId)
                ->where('status', $statusText);
        })
            ->with(['details.competitionUsers' => function ($query) use ($authId) {
                $query->where('user_id', $authId);
            }])
            ->get();

        return $this->success([
            'status_text' => $statusText,
            'competitions' => $competitions,
        ], 'Competitions fetched successfully', 200);
    }




    public function competitionDetail($id)
    {
        // Load competition with details and videos
        $competition = Competition::with(['details', 'videos'])->findOrFail($id);

        // Count participants via competition_details -> competition_users
        $competitionCount = CompetitionUser::whereIn(
            'competition_detail_id',
            CompetitionDetail::where('competition_id', $competition->id)->pluck('id')
        )->count();

        // Get current user's status
        $competitionUserStatus = CompetitionUser::whereIn(
            'competition_detail_id',
            CompetitionDetail::where('competition_id', $competition->id)->pluck('id')
        )
            ->where('user_id', Auth::id())
            ->first();

        // Get exercises linked to the competition (via pivot table) filtered by genz (including 'both')
        $exercises = $competition->exercises()
            ->where(function ($query) use ($competition) {
                $query->where('genz', $competition->genz)
                    ->orWhere('genz', 'both');
            })
            ->get();

        // Append extra data
        $competition->participants = $competitionCount;
        $competition->user_status = $competitionUserStatus->status ?? null;
        $competition->exercises = $exercises;

        return $this->success($competition, 'Competition details fetched successfully', 200);
    }


    public function getCompetitionDetail($id)
    {
        $competition = Competition::with(['details', 'videos', 'exercises' => function ($q) {
            $q->select('exercises.id', 'exercise_category_id', 'name', 'genz', 'description', 'image');
        }])
            ->where('status', 'active')
            ->findOrFail($id);

        return $this->success([
            'competition' => $competition
        ], 'Competition detail fetched successfully', 200);
    }


    public function acceptOrReject(Request $request, $id)
    {
        $competitionUser = CompetitionUser::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'competition_detail_id' => $id,
            ],
            [
                'status' => $request->status,
            ]
        );

        if ($competitionUser) {
            return $this->success($competitionUser, 'Competition status updated successfully', 200);
        }

        return $this->error('Competition not found', [], 404);
    }


    public function getResult()
    {
        $authId = Auth::id();

        // 1. Get all active competition_detail_ids where auth user is participating
        $activeCompetitionDetailIds = CompetitionUser::where('user_id', $authId)
            ->where('status', 'accepted')
            ->pluck('competition_detail_id');

        if ($activeCompetitionDetailIds->isEmpty()) {
            return $this->success([
                'overall_summary' => [],
                'exercise_summary' => [],
            ], 'No competition results found', 200);
        }

        // 2. Get latest competition_detail_id from above (based on competitions.created_at)
        $latestDetail = CompetitionDetail::whereIn('id', $activeCompetitionDetailIds)
            ->with('competition')
            ->orderByDesc('created_at')
            ->first();

        if (!$latestDetail) {
            return $this->success([
                'overall_summary' => [],
                'exercise_summary' => [],
            ], 'No competition results found', 200);
        }

        // 3. Get all users for the latest competition_detail (with totals)
        $latestCompetitionUsers = CompetitionUser::with([
            'user',
            'total',  // hasOne CompetitionUserTotal
        ])
            ->where('competition_detail_id', $latestDetail->id)
            ->get();

        $overallSummary = $latestCompetitionUsers
            ->map(function ($cu) use ($latestDetail) {
                $totalScore = $cu->total->total_score
                    ?? $cu->results->sum('score'); // fallback if no totals yet

                return [
                    'competition_id' => $latestDetail->competition_id,
                    'user_id' => $cu->user->id,
                    'name' => $cu->user->name ?? 'Unknown',
                    'image' => $cu->user->image ?? '',
                    'total_score' => $totalScore,
                    'rank' => $cu->total->rank ?? null,
                ];
            })
            ->sortBy('rank')
            ->values();


        // 4. Exercise summary for ALL competitions the user is in
        $allCompetitionUsers = CompetitionUser::with([
            'user',
            'results.exercise', // get exercise name
            'competitionDetail.competition'
        ])
            ->whereIn('competition_detail_id', $activeCompetitionDetailIds)
            ->get();

        $exerciseSummary = $allCompetitionUsers
            ->flatMap(function ($cu) {
                return $cu->results->map(function ($result) use ($cu) {
                    return [
                        'exercise' => $result->exercise->name ?? 'Unknown Exercise',
                        'competition_id' => $cu->competitionDetail->competition_id,
                        'user_id' => $cu->user->id,
                        'name' => $cu->user->name ?? 'Unknown',
                        'image' => $cu->user->image ?? '',
                        'score' => (float) $result->score,
                    ];
                });
            })
            ->groupBy('exercise')
            ->map(function ($group, $exerciseName) {
                return [
                    'exercise' => $exerciseName,
                    'participants' => $group->values()
                ];
            })
            ->values();

        return $this->success([
            'result' => $overallSummary,
            'summary' => $exerciseSummary,
        ], 'Competition summary data', 200);
    }

    public function writeAppeal(Request $request)
    {
        $request->validate([
            'competition_video_id' => 'required|exists:competition_videos,id',
            'appeal_text' => 'required|string|max:1000',
        ]);

        $competitionVideo = CompetitionVideo::findOrFail($request->competition_video_id);

        // Fetch the competition detail ID that this video belongs to
        $competitionDetailId = \App\Models\CompetitionDetail::where('competition_id', $competitionVideo->competition_id)
            ->value('id');

        if (!$competitionDetailId) {
            return $this->error('Competition detail not found for this video', [], 404);
        }

        // Check if the authenticated user is part of this competition detail
        // $competitionUser = CompetitionUser::where('user_id', Auth::id())
        //     ->where('competition_detail_id', $competitionDetailId)
        //     ->first();

        // if (!$competitionUser) {
        //     return $this->error('You are not participating in this competition', [], 404);
        // }

        // Create the appeal
        $storeAppeal = CompetitionAppeal::create([
            'user_id' => Auth::id(),
            'competition_video_id' => $competitionVideo->id,
            'appeal_text' => $request->appeal_text,
            'status' => 'pending',
        ]);

        if (!$storeAppeal) {
            return $this->error('Failed to submit appeal', [], 500);
        }

        return $this->success('Appeal submitted successfully', 200);
    }

    public function getAppeal($id)
    {
        $authId = Auth::id();

        // Eager load videos with each competition
        $competition = Competition::with('details', 'videos')->findOrFail($id);

        // Get all video IDs for this competition
        $videoIds = $competition->videos->pluck('id')->toArray();

        // Fetch appeals with their related competitionVideo
        $appeals = CompetitionAppeal::with('competitionVideo')
            ->whereIn('competition_video_id', $videoIds)
            ->where('user_id', $authId)
            ->get();

        return $this->success([
            'competition' => $competition,
            'appeals' => $appeals,
        ], 'Appeals retrieved successfully');
    }


    public function viewResult($competitionDetailId)
    {
        $competitionDetail = CompetitionDetail::with([
            'competition:id,name',
            'competitionUsers.user:id,name,email,image',
            'competitionUsers.total' // from competition_user_totals table
        ])->findOrFail($competitionDetailId);

        return response()->json([
            'success' => true,
            'message' => 'Competition detail result fetched successfully',
            'data' => [
                'competition_detail' => [
                    'id' => $competitionDetail->id,
                    'competition' => [
                        'id' => $competitionDetail->competition->id,
                        'title' => $competitionDetail->competition->name
                    ],
                    'coach_name' => $competitionDetail->coach_name,
                    'city' => $competitionDetail->city,
                    'start_date' => $competitionDetail->start_date,
                    'end_date' => $competitionDetail->end_date
                ],
                'users' => $competitionDetail->competitionUsers->map(function ($compUser) {
                    return [
                        'id' => $compUser->user->id,
                        'name' => $compUser->user->name,
                        'email' => $compUser->user->email,
                        'image' => $compUser->user->image ? asset('storage/' . $compUser->user->image) : null,
                        'score' => $compUser->total->total_score ?? null,
                        'rank' => $compUser->total->rank ?? null
                    ];
                })
            ]
        ]);
    }



    public function RulesOfCount()
    {
        $data = RulesOfCounting::with('competition')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 201);
    }

    public function RulesOfCountDetail($id)
    {
        $data = RulesOfCounting::findOrFail($id)->with('competition')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 201);
    }
}
