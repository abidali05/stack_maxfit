<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisations;
use App\Models\CompetitionVideo;
use App\Models\CompetitionAppeal;
use App\Models\CompetitionResult;
use App\Models\OrganisationTypes;
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
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'age_group'     => 'required|string|max:100',
            'coach_name'    => 'required|string|max:100',
            'country'       => 'required|string|max:100',
            'city'          => 'required|string|max:100',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'time_allowed'  => 'required|numeric|min:1',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'genz' => 'required|in:motherfits,fatherfits',
            'org_type' => 'required|exists:organisation_types,id', // Validate
            'org' => 'required|exists:organisations,id', // Validate
            'genz'         => 'required|in:motherfits,fatherfits',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/competitions'), $filename);
            $validated['image'] = 'uploads/competitions/' . $filename;
        }

        $validated['genz'] = $request->input('genz');

        $this->competitionOption->store_competition($validated);

        Toastr::success('Competition created successfully', 'Success');
        return redirect()->route('competitions.index');
    }


    public function edit(string $id)
    {
        $organizations = Organisations::all();
        $organizationTypes = OrganisationTypes::all();

        $competition = $this->competitionOption->get_competition($id);
        return view('competitions.edit', compact('competition','organizations','organizationTypes'));
    }

    public function show(string $id)
    {
        $competition = $this->competitionOption->view_competition($id);
        return view('competitions.view', compact('competition'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'age_group'     => 'required|string|max:255',
            'coach_name'    => 'required|string|max:255',
            'country'       => 'required|string|max:255',
            'city'          => 'required|string|max:255',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'time_allowed'  => 'required|integer|min:1',
            'status'        => 'nullable|in:active,inactive',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description'   => 'nullable|string',
            'org_type' => 'required|exists:organisation_types,id', // Validate
            'org' => 'required|exists:organisations,id', // Validate
            'genz'         => 'required|in:motherfits,fatherfits',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image before saving new one
            $competition = $this->competitionOption->get_competition($id);
            if ($competition->image && Storage::disk('public')->exists($competition->image)) {
                Storage::disk('public')->delete($competition->image);
            }

            $imagePath = $request->file('image')->store('competitions', 'public');
            $validated['image'] = $imagePath;
        }

        $videos = $request->hasFile('videos') ? $request->file('videos') : null;

        $this->competitionOption->update_competition($id, $validated, $videos);

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
