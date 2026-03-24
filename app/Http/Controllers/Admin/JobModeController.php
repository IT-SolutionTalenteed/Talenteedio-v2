<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobMode;
use Illuminate\Http\Request;

class JobModeController extends Controller
{
    public function index()
    {
        return response()->json(JobMode::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_modes,name',
        ]);

        return response()->json(JobMode::create($validated), 201);
    }

    public function show(JobMode $jobMode)
    {
        return response()->json($jobMode);
    }

    public function update(Request $request, JobMode $jobMode)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_modes,name,' . $jobMode->id,
        ]);

        $jobMode->update($validated);

        return response()->json($jobMode);
    }

    public function destroy(JobMode $jobMode)
    {
        $jobMode->delete();

        return response()->json(['message' => 'Mode de travail supprimé avec succès']);
    }
}
