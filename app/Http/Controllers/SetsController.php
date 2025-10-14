<?php

namespace App\Http\Controllers;

use App\Models\Set;
use App\Models\Exercise;
use Illuminate\Http\Request;

class SetsController extends Controller
{
    public function index()
    {
        $sets = Set::with('exercises')->get();
        return view('sets.index', compact('sets'));
    }

    public function create()
    {
        return view('sets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'genz' => 'required|in:fatherfits,motherfits',
            'exercises' => 'required|array',
            'exercises.*' => 'exists:exercises,id'
        ]);

        $set = Set::create([
            'name' => $request->name,
            'genz' => $request->genz
        ]);

        $set->exercises()->attach($request->exercises);

        return redirect()->route('sets.index')->with('success', 'Set created successfully!');
    }

    public function edit(string $id)
    {
        $set = Set::with('exercises')->findOrFail($id);
        return view('sets.edit', compact('set'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'genz' => 'required|in:fatherfits,motherfits',
            'exercises' => 'required|array',
            'exercises.*' => 'exists:exercises,id'
        ]);

        $set = Set::findOrFail($id);
        $set->update([
            'name' => $request->name,
            'genz' => $request->genz
        ]);

        $set->exercises()->sync($request->exercises);

        return redirect()->route('sets.index')->with('success', 'Set updated successfully!');
    }

    public function destroy(string $id)
    {
        $set = Set::findOrFail($id);
        $set->delete();
        return redirect()->route('sets.index')->with('success', 'Set deleted successfully!');
    }

    public function getExercisesByGenz(Request $request)
    {
        $exercises = Exercise::where('genz', $request->genz)
        ->orWhere('genz', 'both')
        ->get();
        return response()->json($exercises);
    }
}
