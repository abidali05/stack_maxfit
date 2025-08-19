<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:branches,email',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string',
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('branches', 'public');
        }

        $validated['password'] = Hash::make('password');

        Branch::create($validated);
        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:branches,email,' . $branch->id,
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            // delete old image if exists
            if ($branch->image && Storage::disk('public')->exists($branch->image)) {
                Storage::disk('public')->delete($branch->image);
            }
            $validated['image'] = $request->file('image')->store('branches', 'public');
        } else {
            $validated['image'] = $branch->image;
        }

        $validated['password'] = Hash::make('password');

        $branch->update($validated);
        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        if ($branch->image && Storage::disk('public')->exists($branch->image)) {
            Storage::disk('public')->delete($branch->image);
        }
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }
}
