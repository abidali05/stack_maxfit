<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\CompetitionUser;
use App\Models\CompetitionDetail;
use Illuminate\Support\Facades\Log;
use App\Models\CompetitionUserTotal;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessCompetitionResults implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $competitionId;

    public function __construct($competitionId)
    {
        $this->competitionId = $competitionId;
    }

    public function handle()
    {
        Log::info("Starting rank & result calculation for competition_id: {$this->competitionId}");

        // Get all competition_detail IDs for this competition
        $detailIds = CompetitionDetail::where('competition_id', $this->competitionId)->pluck('id');

        // Get all competition users (joined to results)
        $users = CompetitionUser::with('total')
            ->whereIn('competition_detail_id', $detailIds)
            ->withSum('results', 'score') // 'results' relation is correct
            ->get()
            ->sortByDesc('results_sum_score')
            ->values();
            Log::info($users);
        foreach ($users as $index => $user) {
            $score = $user->results_sum_score ?? 0;
            // $status = $score >= 50 ? 'passed' : 'failed'; // you can customize threshold

            CompetitionUserTotal::updateOrCreate(
                ['competition_user_id' => $user->id],
                [
                    'total_score' => $score,
                    'rank' => $index + 1,
                    // 'status' => $status
                ]
            );

            Log::info("Updated user total", [
                'competition_user_id' => $user->id,
                'total_score' => $score,
                'rank' => $index + 1,
                // 'status' => $status
            ]);
        }

        Log::info("Completed rank & result calculation for competition_id: {$this->competitionId}");
    }
}
