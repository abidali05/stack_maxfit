<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Constraint\Count;

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
        $country = Country::all();
        $cities = City::all();
        return view('branches.create', compact('country', 'cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'email'       => 'required|email|unique:branches,email',
                'phone'       => 'required|string|max:20',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'bio'         => 'nullable|string',
                'address'     => 'required|string|max:255',
                'country_id'  => 'required|exists:countries,id',
                'city_id'     => 'required|exists:cities,id',
                'status'      => 'nullable|in:active,inactive',
                'percentage'  => 'required|numeric|min:0|max:100', // new field
            ]);

            // handle image
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('branches', 'public');
            }

            // default password
            $validated['password'] = Hash::make('password');

            Branch::create($validated);

            return redirect()
                ->route('branches.index')
                ->with('success', 'Branch created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create branch. ' . $e->getMessage());
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $branch   = Branch::findOrFail($id);
        $countries = Country::all();
        $cities    = City::all();

        return view('branches.edit', compact('branch', 'countries', 'cities'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:branches,email,' . $branch->id,
            'phone'      => 'nullable|string|max:20',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio'        => 'nullable|string',
            'address'    => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'city_id'    => 'required|exists:cities,id',
            'status'     => 'required|in:active,inactive',
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // handle image
        if ($request->hasFile('image')) {
            if ($branch->image && Storage::disk('public')->exists($branch->image)) {
                Storage::disk('public')->delete($branch->image);
            }
            $validated['image'] = $request->file('image')->store('branches', 'public');
        } else {
            $validated['image'] = $branch->image;
        }

        // ⚠️ Don’t reset password on update unless intentional
        // $validated['password'] = Hash::make('password');

        $branch->update($validated);

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branch updated successfully.');
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
