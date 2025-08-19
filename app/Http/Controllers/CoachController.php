<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CoachController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coaches = Coach::all();
        return view('coaches.index', compact('coaches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('coaches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:coaches,email',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string',
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('coaches', 'public');
        }

        $validated['password'] = Hash::make('password');

        Coach::create($validated);
        return redirect()->route('coaches.index')->with('success', 'Coach created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coach $coach)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $coach = Coach::findOrFail($id);
        return view('coaches.edit', compact('coach'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $coach = Coach::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:coaches,email,' . $coach->id,
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string',
        ]);
        if ($request->hasFile('image')) {
            // delete old image if exists
            if ($coach->image && Storage::disk('public')->exists($coach->image)) {
                Storage::disk('public')->delete($coach->image);
            }
            $validated['image'] = $request->file('image')->store('coaches', 'public');
        } else {
            $validated['image'] = $coach->image;
        }

        $validated['password'] = Hash::make('password');

        $coach->update($validated);
        return redirect()->route('coaches.index')->with('success', 'Coach updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $coach = Coach::findOrFail($id);
        if ($coach->image && Storage::disk('public')->exists($coach->image)) {
            Storage::disk('public')->delete($coach->image);
        }
        $coach->delete();
        return redirect()->route('coaches.index')->with('success', 'Coach deleted successfully.');
    }
}
