<?php

namespace App\Http\Controllers;

use App\Models\Coach;
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
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\QueryException;
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
        $coaches = Coach::all();

        return view('competitions.create', compact('organizations', 'organizationTypes', 'coaches'));
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
            'genz' => 'required|in:motherfits,fatherfits',
            'country' => 'required|string|max:100',
            'org_type' => 'nullable|exists:organisation_types,id',
            'org' => 'nullable|exists:organisations,id',
            'time_allowed' => 'nullable|numeric|min:1',
            'coach_id' => 'required|array|min:1',
            'coach_id.*' => 'required|string|max:100',
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
            'youtube_links' => 'array',
            'youtube_links.*' => 'nullable|url',
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
            foreach ($validated['coach_id'] as $index => $coach_id) {
                $detailData = [
                    'competition_id' => $competition->id,
                    'coach_id' => $coach_id,
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

            // Store YouTube video links
            if (!empty($validated['youtube_links'])) {
                foreach ($validated['youtube_links'] as $link) {
                    if (!empty($link)) {
                        CompetitionVideo::create([
                            'competition_id' => $competition->id,
                            'video_file' => $link,
                        ]);
                    }
                }
            }

            // Sync exercises with competition
            $exerciseIds = Exercise::whereIn('genz', [$validated['genz'], 'both'])
                ->pluck('id');
            $competition->exercises()->sync($exerciseIds);

            DB::commit();
            Toastr::success('Competition and details created successfully', 'Success');
            return redirect()->route('competitions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Failed to create competition: ' . $e->getMessage(), 'Error');
            return back()->withErrors(['error' => 'Failed to create competition.']);
        }
    }


    public function edit($id)
    {
        try {
            $competition = Competition::with('details', 'videos')->findOrFail($id);
            $organizationTypes = OrganisationTypes::all();
            $organizations = Organisations::all();
            $coaches = \App\Models\Coach::all();
            return view('competitions.edit', compact('competition', 'organizationTypes', 'organizations', 'coaches'));
        } catch (\Exception $e) {
            Toastr::error('Failed to load form data: ' . $e->getMessage(), 'Error');
            return back();
        }
    }

    public function show(string $id)
    {
        $competitionDetail = CompetitionDetail::where('competition_id', $id)->with('coach')->get();
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
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age_group' => 'required|numeric|min:1', // Changed to numeric to match store
            'genz' => 'required|in:motherfits,fatherfits',
            'country' => 'required|string|max:100',
            'org_type' => 'nullable|exists:organisation_types,id',
            'org' => 'nullable|exists:organisations,id',
            'time_allowed' => 'nullable|numeric|min:1',
            'status' => 'required|in:active,inactive', // Made required to match form
            'youtube_links' => 'array',
            'youtube_links.*' => 'nullable|url',
            'detail_ids' => 'array',
            'detail_ids.*' => 'nullable|integer|exists:competition_details,id',
            'coach_id' => 'required|array|min:1',
            'coach_id.*' => 'required|integer|exists:coaches,id', // Changed to integer to match database
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
            // Update the competition record
            $competition = Competition::findOrFail($id);
            $competitionData = [
                'name' => $validated['name'],
                'age_group' => $validated['age_group'],
                'genz' => $validated['genz'],
                'country' => $validated['country'],
                'time_allowed' => $validated['time_allowed'] ?? null,
                'org_type' => $validated['org_type'] ?? null,
                'org' => $validated['org'] ?? null,
                'status' => $validated['status'],
            ];
            $competition->update($competitionData);

            // Handle competition details
            $existingDetailIds = $competition->details->pluck('id')->toArray();
            $submittedDetailIds = array_filter($validated['detail_ids'] ?? [], fn($id) => !empty($id));
            $detailsToDelete = array_diff($existingDetailIds, $submittedDetailIds);

            // Delete removed competition details
            if (!empty($detailsToDelete)) {
                foreach ($detailsToDelete as $detailId) {
                    $detail = CompetitionDetail::find($detailId);
                    if ($detail && $detail->image) {
                        Storage::disk('public')->delete($detail->image);
                    }
                    $detail?->delete();
                }
            }

            // Update or create competition details
            foreach ($validated['coach_id'] as $index => $coachId) {
                $detailData = [
                    'competition_id' => $competition->id,
                    'coach_id' => $coachId,
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
                    // Delete old image if updating an existing detail
                    if (isset($validated['detail_ids'][$index]) && !empty($validated['detail_ids'][$index])) {
                        $oldDetail = CompetitionDetail::find($validated['detail_ids'][$index]);
                        if ($oldDetail && $oldDetail->image) {
                            Storage::disk('public')->delete($oldDetail->image);
                        }
                    }
                }

                // Update or create detail
                if (isset($validated['detail_ids'][$index]) && !empty($validated['detail_ids'][$index])) {
                    CompetitionDetail::where('id', $validated['detail_ids'][$index])->update($detailData);
                } else {
                    CompetitionDetail::create($detailData);
                }
            }

            // Handle YouTube video links
            $competition->videos()->delete(); // Delete existing videos
            if (!empty($validated['youtube_links'])) {
                foreach ($validated['youtube_links'] as $link) {
                    if (!empty($link)) {
                        CompetitionVideo::create([
                            'competition_id' => $competition->id,
                            'video_file' => $link,
                        ]);
                    }
                }
            }

            // Sync exercises with competition
            $exerciseIds = Exercise::whereIn('genz', [$validated['genz'], 'both'])->pluck('id');
            $competition->exercises()->sync($exerciseIds);

            DB::commit();
            Toastr::success('Competition and details updated successfully', 'Success');
            return redirect()->route('competitions.index');
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == '23000') {
                Toastr::error('Invalid data provided (e.g., coach or organization not found).', 'Error');
                Log::error('Competition update failed: ' . $e->getMessage(), ['validated' => $validated]);
            } else {
                Toastr::error('Failed to update competition: ' . $e->getMessage(), 'Error');
                Log::error('Competition update failed: ' . $e->getMessage());
            }
            return back()->withErrors(['error' => 'Failed to update competition.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Failed to update competition: ' . $e->getMessage(), 'Error');
            Log::error('Competition update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update competition.']);
        }
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
