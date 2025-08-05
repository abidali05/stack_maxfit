<?php

namespace App\Repositories;

use App\Models\Competition;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Contracts\CompetitionRepositoryInterface;

class CompetitionRepository implements CompetitionRepositoryInterface
{
    protected $model;

    public function __construct(Competition $model)
    {
        $this->model = $model;
    }

    public function get_competitions()
    {
        return $this->model::get();
    }

    public function store_competition(array $data)
    {
        return $this->model::create($data);
    }

    public function get_competition($id)
    {
        return $this->model::find($id);
    }

    public function view_competition($id)
    {
        return $this->model::with('competitionUsers', 'competitionUsers.competitionResult')->find($id);
    }

    public function update_competition($id, array $data, $videos = null)
    {
        $competition = $this->model::with('videos')->findOrFail($id);

        $competition->update($data);

        if ($videos && count($videos) > 0) {
            foreach ($competition->videos as $video) {
                if (Storage::disk('public')->exists(str_replace('storage/', '', $video->video_file))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $video->video_file));
                }
                $video->delete();
            }

            foreach ($videos as $videoFile) {
                $videoPath = $videoFile->store('competition_videos', 'public');
                $competition->videos()->create([
                    'video_file' => $videoPath,
                ]);
            }
        }

        return $competition;
    }


    public function delete_competition($id)
    {
        return $this->model::where('id', $id)->delete();
    }
}
