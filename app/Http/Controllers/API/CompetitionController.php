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
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CompetitionController extends Controller
{
    public function getCompetition()
    {
        $authId = Auth::id();

        $competitions = Competition::with('videos')
            ->where('status', 'active')
            ->get()
            ->map(function ($competition) use ($authId) {
                // Load only exercises that match the competition's genz
                $competition->exercises = Exercise::where('genz', $competition->genz)
                    ->select('id', 'exercise_category_id', 'name', 'genz', 'description', 'image')
                    ->get();

                // Add user status for this competition (0 = rejected, 1 = accepted, null = not responded)
                $competition->status_type = CompetitionUser::where('competition_id', $competition->id)
                    ->where('user_id', $authId)
                    ->value('status');

                return $competition;
            });

        return $this->success([
            'competitions' => $competitions,
        ], 'Competitions fetched successfully', 200);
    }

    public function getCompetitionStatus($status)
    {
        $authId = Auth::id();

        $competitions = Competition::with('videos')
            ->where('status', 'active')
            ->get()
            ->filter(function ($competition) use ($authId, $status) {
                // Only keep competitions where the user has the given status
                return CompetitionUser::where('competition_id', $competition->id)
                    ->where('user_id', $authId)
                    ->where('status', $status)
                    ->exists();
            })
            ->map(function ($competition) use ($authId, $status) {
                // Load exercises for the same genz
                $competition->exercises = Exercise::where('genz', $competition->genz)
                    ->select('id', 'exercise_category_id', 'name', 'genz', 'description', 'image')
                    ->get();

                // Set status_type (it will always match $status due to filter)
                $competition->status_type = (string) $status;

                return $competition;
            })
            ->values(); // Reset collection keys

        return $this->success([
            'competitions' => $competitions,
        ], 'Competitions fetched successfully', 200);
    }


    public function competitionDetail($id)
    {
        $competition = Competition::with(['videos'])->findOrFail($id);

        // Count participants
        $competitionCount = CompetitionUser::where('competition_id', $id)->count();

        // Get user's status
        $competitionUserStatus = CompetitionUser::where('competition_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        // Get exercises based on genz
        $exercises = Exercise::where('genz', $competition->genz)->get();

        // Append extra data to competition object
        $competition->participants = $competitionCount;
        $competition->user_status = $competitionUserStatus->status ?? null;
        $competition->exercises = $exercises;

        return $this->success($competition, 'Competition details fetched successfully', 200);
    }

    public function getCompetitionDetail($id)
    {
        $competition = Competition::with('videos')
            ->where('status', 'active')
            ->findOrFail($id);

        $exercises = Exercise::where('genz', $competition->genz)
            ->select('id', 'exercise_category_id', 'name', 'genz', 'description', 'image')
            ->get();

        $competition->exercises = $exercises;

        return $this->success([
            'competition' => $competition
        ], 'Competition detail fetched successfully', 200);
    }

    public function acceptOrReject(Request $request, $id)
    {
        $competitionUser = CompetitionUser::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'competition_id' => $id,
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

        // 1. Get all active competition IDs where auth user is participating
        $activeCompetitionUserIds = CompetitionUser::where('user_id', $authId)
            ->where('status', '1')
            ->pluck('competition_id');

        // 2. Get the latest competition ID from the above
        $latestCompetitionId = Competition::whereIn('id', $activeCompetitionUserIds)
            ->orderByDesc('created_at')
            ->value('id');

        if (!$latestCompetitionId) {
            return $this->success([
                'overall_summary' => [],
                'exercise_summary' => [],
            ], 'No competition results found', 200);
        }

        // 3. Get all users with results in the latest competition
        $latestCompetitionUsers = CompetitionUser::with(['user', 'competitionResult'])
            ->where('competition_id', $latestCompetitionId)
            ->get()
            ->filter(fn($user) => isset($user->competitionResult->position));

        $overallSummary = $latestCompetitionUsers->sortBy(fn($user) => (int) $user->competitionResult->position)
            ->values()
            ->map(function ($user) {
                return [
                    'competition_d' => $user->competition->id,
                    'user_id' => $user->user->id,
                    'name' => $user->user->name ?? 'Unknown',
                    'image' => $user->user->image ?? '',
                    'position' => (int) $user->competitionResult->position,
                    'percentage' => (float) $user->competitionResult->percentage,
                ];
            });

        // 4. Get all users with results for ALL active competitions the user is in
        $allCompetitionUsers = CompetitionUser::with(['user', 'competitionResult'])
            ->whereIn('competition_id', $activeCompetitionUserIds)
            ->get();

        // 5. Group by exercise name and build the exercise_summary
        $exerciseSummary = $allCompetitionUsers->groupBy(fn($user) => $user->competition->name ?? 'Unknown Exercise')
            ->map(function ($users, $exerciseName) {
                return [
                    'exercise' => $exerciseName,
                    'participants' => $users->map(function ($user) {
                        return [
                            'competition_id' => $user->competition->id,
                            'user_id' => $user->user->id,
                            'name' => $user->user->name ?? 'Unknown',
                            'image' => $user->user->image ?? '',
                            'percentage' => (float) $user->competitionResult->percentage,
                            'per_min' => (float) $user->competitionResult->per_min,
                        ];
                    })->values()
                ];
            })->values();

        return $this->success([
            'resullt' => $overallSummary,
            'summary' => $exerciseSummary,
        ], 'Competition summary Data', 200);
    }

    public function getAppeal($id)
    {
        $authId = Auth::id();

        $competition = Competition::with('videos')->findOrFail($id);

        // Get all competition video IDs for this competition
        $videoIds = $competition->videos->pluck('id');

        // Fetch appeals for these videos by the authenticated user
        $appeals = CompetitionAppeal::with('competitionVideo')
            ->whereIn('competition_video_id', $videoIds)
            ->where('user_id', $authId)
            ->get();

        return $this->success([
            'competition' => $competition,
            'appeals' => $appeals,
        ], 'Appeals retrieved successfully');
    }

    public function writeAppeal(Request $request)
    {
        $request->validate([
            'competition_video_id' => 'required|exists:competition_videos,id',
            'appeal_text' => 'required|string|max:1000',
        ]);

        $competitionVideo = CompetitionVideo::findOrFail($request->competition_video_id);

        $competitionUser = CompetitionUser::where('user_id', Auth::id())
            ->where('competition_id', $competitionVideo->competition_id)
            ->first();

        if (!$competitionUser) {
            return $this->error('You are not participating in this competition', 404);
        }

        $storeAppeal = CompetitionAppeal::create([
            'user_id' => Auth::id(),
            'competition_video_id' => $competitionVideo->id,
            'appeal_text' => $request->appeal_text,
            'status' => 'pending',
        ]);
        if (!$storeAppeal) {
            return $this->error('Failed to submit appeal', 500);
        }

        return $this->success('Appeal submitted successfully', 200);
    }

    public function viewResult($id)
    {
        $competition = Competition::with(['competitionUsers.user', 'competitionUsers.competitionResult'])
            ->findOrFail($id);

        $competition->users = $competition->competitionUsers->map(function ($compUser) {
            return [
                'name' => $compUser->user->name ?? null,
                'image' => $compUser->user->image ?? null,
                'position' => $compUser->competitionResult->position ?? null,
                'percentage' => $compUser->competitionResult->percentage ?? null,
            ];
        });

        return $this->success($competition->users, 'Competition result fetched successfully', 200);
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
