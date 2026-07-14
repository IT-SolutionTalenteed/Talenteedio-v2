<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index()
    {
        return response()->json(Country::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255|unique:countries,name',
            'region' => ['required', Rule::in(['europe', 'afrique', 'autre'])],
        ]);

        return response()->json(Country::create($validated), 201);
    }

    public function show(Country $country)
    {
        return response()->json($country);
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', Rule::unique('countries', 'name')->ignore($country->id)],
            'region' => ['required', Rule::in(['europe', 'afrique', 'autre'])],
        ]);

        $country->update($validated);

        return response()->json($country);
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return response()->json(['message' => 'Pays supprimé avec succès']);
    }
}
