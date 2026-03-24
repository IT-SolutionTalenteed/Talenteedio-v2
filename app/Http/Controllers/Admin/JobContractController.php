<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobContract;
use Illuminate\Http\Request;

class JobContractController extends Controller
{
    public function index()
    {
        return response()->json(JobContract::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_contracts,name',
        ]);

        $jobContract = JobContract::create($validated);

        return response()->json($jobContract, 201);
    }

    public function show(JobContract $jobContract)
    {
        return response()->json($jobContract);
    }

    public function update(Request $request, JobContract $jobContract)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_contracts,name,' . $jobContract->id,
        ]);

        $jobContract->update($validated);

        return response()->json($jobContract);
    }

    public function destroy(JobContract $jobContract)
    {
        $jobContract->delete();

        return response()->json(['message' => 'Contrat de travail supprimé avec succès']);
    }
}
