<?php

namespace App\Http\Controllers;

use getID3;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Contracts\ExerciseRepositoryInterface;

class ExerciseController extends Controller
{
    protected $exe;

    public function __construct(ExerciseRepositoryInterface $exe)
    {
        $this->exe = $exe;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exercises = $this->exe->get_exercises();
        return view('exercises.index', compact('exercises'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $exercises = $this->exe->get_exercise_caetegories();
        return view('exercises.create', compact('exercises'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'genz' => 'required|string|max:255',
            'exercise_category_id' => 'required|integer',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_file' => 'nullable|url'
        ]);

        // Handling image upload
        if (isset($validated['image']) && $validated['image']->isValid()) {
            $imageName = time() . '.' . $validated['image']->getClientOriginalExtension();
            $path = $validated['image']->storeAs('uploads/exercises', $imageName, 'public');
            $validated['image'] = $path;
        }

        // Handling video upload and extracting duration
        // if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
        //     $video = $request->file('video_file');  // Corrected here to reference 'video_file'
        //     $videoName = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
        //     $videoPath = $video->storeAs('uploads/exercises/videos', $videoName, 'public');

        //     // Extracting video duration using getID3
        //     $getID3 = new getID3;  // You should have this namespace
        //     $videoFile = $getID3->analyze(storage_path('app/public/' . $videoPath));  // Path is relative to storage
        //     $duration_seconds = $videoFile['playtime_seconds'];  // Video duration in seconds

        //     $validated['video_file'] = $videoPath;
        //     $validated['video_time'] = gmdate("H:i:s", $duration_seconds);  // Format as HH:MM:SS
        // }

        // Creating exercise record with validated data
        $this->exe->create_exercise($validated);
        Toastr::success('Exercise created successfully', 'Success');

        return redirect()->route('exercises.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(Exercise $exercise)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exercise $exercise)
    {
        $exercises = $this->exe->get_exercise_caetegories();
        return view('exercises.edit', compact('exercises', 'exercise'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Exercise $exercise)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'genz' => 'required|string|max:255',
            'exercise_category_id' => 'required|integer',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'video_file' => 'nullable|url'
        ]);

        // Handling Image Upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Delete old image
            if ($exercise->image && Storage::disk('public')->exists($exercise->image)) {
                Storage::disk('public')->delete($exercise->image);
            }

            $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('uploads/exercises', $imageName, 'public');
            $validated['image'] = $path;
        } else {
            unset($validated['image']); // Remove image field from validated if no new image
        }

        // Handling Video File Upload
        // if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
        //     // Delete old video
        //     if ($exercise->video_file && Storage::disk('public')->exists($exercise->video_file)) {
        //         Storage::disk('public')->delete($exercise->video_file);
        //     }

        //     // Handle new video upload
        //     $video = $request->file('video_file');
        //     $videoName = time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
        //     $videoPath = $video->storeAs('uploads/exercises/videos', $videoName, 'public');

        //     // Extract video duration
        //     $getID3 = new getID3;  // Using getID3 to analyze video and get duration
        //     $videoFile = $getID3->analyze(storage_path('app/public/' . $videoPath));  // Get full path
        //     $duration_seconds = $videoFile['playtime_seconds'];  // Video duration in seconds

        //     // Save video path and duration
        //     $validated['video_file'] = $videoPath;
        //     $validated['video_time'] = gmdate("H:i:s", $duration_seconds);  // Convert to HH:MM:SS format
        // } else {
        //     unset($validated['video_file']); // Remove video field if no new video
        //     unset($validated['video_time']); // Remove video_time if no new video
        // }

        // Update the exercise
        $this->exe->update_exercise($exercise->id, $validated);

        Toastr::success('Exercise updated successfully', 'Success');
        return redirect()->route('exercises.index');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {
        if (!$exercise) {
            return false;
        }

        if ($exercise->image) {
            $imagePath = str_replace(asset('storage') . '/', '', $exercise->image);
            $fullPath = public_path('storage/' . $imagePath);

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $this->exe->delete_exercise($exercise->id);

        Toastr::success('Exercise deleted successfully', 'Success');
        return redirect()->route('exercises.index');
    }
}
