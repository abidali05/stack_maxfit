<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Models\Organisations;
use App\Models\CompetitionVideo;
use App\Models\CompetitionAppeal;
use App\Models\CompetitionDetail;
use App\Models\CompetitionResult;
use App\Models\OrganisationTypes;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Contracts\CompetitionRepositoryInterface;

class CompetitionController extends Controller
{
    protected $competitionOption;

    public function __construct(CompetitionRepositoryInterface $competitionOption)
    {
        $this->competitionOption = $competitionOption;
    }

    public function index()
    {
        $competitions = $this->competitionOption->get_competitions();
        return view('competitions.index', compact('competitions'));
    }

    public function create()
    {
        $organizations = Organisations::all();
        $organizationTypes = OrganisationTypes::all();

        return view('competitions.create', compact('organizations', 'organizationTypes'));
    }

    public function getOrganizationsByType($org_type_id)
    {
        $organizations = Organisations::where('type', $org_type_id)->get(['id', 'name']);
        return response()->json($organizations);
    }


    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age_group' => 'required|numeric|min:1',
            'genz' => 'required|in:motherfits,fatherfits,both',
            'country' => 'required|string|max:100',
            'org_type' => 'nullable|exists:organisation_types,id',
            'org' => 'nullable|exists:organisations,id',
            'time_allowed' => 'nullable|numeric|min:1',
            'coach_name' => 'required|array|min:1',
            'coach_name.*' => 'required|string|max:100',
            'cities' => 'required|array|min:1',
            'cities.*' => 'required|string|max:100',
            'start_date' => 'nullable|array',
            'start_date.*' => 'nullable|date',
            'end_date' => 'nullable|array',
            'end_date.*' => 'nullable|date',
            'start_time' => 'nullable|array',
            'start_time.*' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|array',
            'end_time.*' => 'nullable|date_format:H:i',
            'image' => 'nullable|array',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',
        ]);

        // Custom validation for end_date and end_time
        foreach ($validated['cities'] as $index => $city) {
            if (
                isset($validated['start_date'][$index], $validated['end_date'][$index]) &&
                $validated['start_date'][$index] && $validated['end_date'][$index] &&
                strtotime($validated['end_date'][$index]) < strtotime($validated['start_date'][$index])
            ) {
                return back()->withErrors(['end_date.' . $index => 'End date must be on or after start date.']);
            }
            if (
                isset($validated['start_date'][$index], $validated['end_date'][$index], $validated['start_time'][$index], $validated['end_time'][$index]) &&
                $validated['start_date'][$index] == $validated['end_date'][$index] &&
                $validated['start_time'][$index] && $validated['end_time'][$index] &&
                strtotime($validated['end_time'][$index]) <= strtotime($validated['start_time'][$index])
            ) {
                return back()->withErrors(['end_time.' . $index => 'End time must be after start time.']);
            }
        }

        // Begin database transaction
        DB::beginTransaction();
        try {
            // Create a single competition record
            $competitionData = [
                'name' => $validated['name'],
                'age_group' => $validated['age_group'],
                'genz' => $validated['genz'],
                'country' => $validated['country'],
                'time_allowed' => $validated['time_allowed'] ?? null,
                'org_type' => $validated['org_type'] ?? null,
                'org' => $validated['org'] ?? null,
                'status' => 'active',
            ];

            $competition = Competition::create($competitionData);

            // Create competition details for each set
            foreach ($validated['coach_name'] as $index => $coach_name) {
                $detailData = [
                    'competition_id' => $competition->id,
                    'coach_name' => $coach_name,
                    'city' => $validated['cities'][$index] ?? null,
                    'start_date' => $validated['start_date'][$index] ?? null,
                    'end_date' => $validated['end_date'][$index] ?? null,
                    'start_time' => $validated['start_time'][$index] ?? null,
                    'end_time' => $validated['end_time'][$index] ?? null,
                    'description' => $validated['description'][$index] ?? null,
                ];

                // Handle image upload
                if (isset($request->file('image')[$index]) && $request->file('image')[$index]) {
                    $detailData['image'] = $request->file('image')[$index]->store('competitionDetails', 'public');
                }

                CompetitionDetail::create($detailData);
            }

            DB::commit();
            Toastr::success('Competition and details created successfully', 'Success');
            return redirect()->route('competitions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Failed to create competition: ' . $e->getMessage(), 'Error');
            return back()->withErrors(['error' => 'Failed to create competition.']);
        }
    }


    public function edit(string $id)
    {
        $organizations = Organisations::all();
        $organizationTypes = OrganisationTypes::all();

        $competition = $this->competitionOption->get_competition($id);
        return view('competitions.edit', compact('competition', 'organizations', 'organizationTypes'));
    }

    public function show(string $id)
    {
        // $competition = $this->competitionOption->view_competition($id);
        // $exercises = Exercise::where('genz', $competition->genz)->get();
        $competitionDetail = CompetitionDetail::where('competition_id', $id)->get();
        return view('competitions.view', compact('competitionDetail', 'id'));
    }

    public function storeResults(Request $request, string $id)
    {
        $competition = $this->competitionOption->view_competition($id);
        $exercises = Exercise::where('genz', $competition->genz)->pluck('id')->toArray();

        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.competition_user_id' => 'required|exists:competition_users,id',
            'results.*.exercise_id' => 'required|in:' . implode(',', $exercises),
            'results.*.score' => 'required|numeric|min:0',
        ]);

        foreach ($validated['results'] as $result) {
            CompetitionResult::updateOrCreate(
                [
                    'competition_user_id' => $result['competition_user_id'],
                    'exercise_id' => $result['exercise_id'],
                ],
                [
                    'score' => $result['score'],
                ]
            );
        }

        Toastr::success('Results saved successfully', 'Success');
        return redirect()->route('competitions.show', $id);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'age_group'     => 'required|string|max:255',
            'country'       => 'required|string|max:255',
            'time_allowed'  => 'nullable|integer|min:1',
            'status'        => 'nullable|in:active,inactive',
            'org_type' => 'nullable|exists:organisation_types,id', // Validate
            'org' => 'nullable|exists:organisations,id', // Validate
            'youtube_links.*' => 'nullable|url'
        ]);

        $youtubeLinks = $request->youtube_links ?? [];
        unset($validated['youtube_links']);

        $this->competitionOption->update_competition($id, $validated, $youtubeLinks);

        Toastr::success('Competition updated successfully', 'Success');
        return redirect()->route('competitions.index');
    }


    public function destroy(string $id)
    {
        $competition = $this->competitionOption->get_competition($id);

        // Delete image
        if ($competition->image && Storage::disk('public')->exists($competition->image)) {
            Storage::disk('public')->delete($competition->image);
        }

        // Delete videos (assuming a Competition hasMany videos relationship)
        foreach ($competition->videos as $video) {
            if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
                Storage::disk('public')->delete($video->video_path);
            }
            $video->delete();
        }

        // Delete competition itself
        $this->competitionOption->delete_competition($id);

        Toastr::success('Competition deleted successfully', 'Success');
        return redirect()->route('competitions.index');
    }

    public function appeals()
    {
        $appeals = CompetitionAppeal::with('competitionVideo.competition', 'user')
            ->get();
        return view('competitions.appeals', compact('appeals'));
    }

    public function appealDetails(string $id)
    {
        $competition = $this->competitionOption->get_competition($id);
        return view('competitions.appeal_details', compact('competition'));
    }

    public function competitionVideos()
    {
        $videos = CompetitionVideo::all();
        return view('competitions.videos', compact('videos'));
    }

     public function competitionAppeals()
    {
        $appeals = CompetitionAppeal::all();
        return view('competitions.appeals', compact('appeals'));
    }

    public function destroyAppeal($id)
    {
        $appeal = CompetitionAppeal::findOrFail($id);
        $appeal->delete();
        Toastr::success('Appeal deleted successfully', 'Success');
        return redirect()->back();
    }

    public function updateAppealStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);
        $appeal = CompetitionAppeal::findOrFail($id);
        $appeal->status = $request->status;
        $appeal->save();
        Toastr::success('Appeal status updated successfully', 'Success');
        return redirect()->back();
    }
}
