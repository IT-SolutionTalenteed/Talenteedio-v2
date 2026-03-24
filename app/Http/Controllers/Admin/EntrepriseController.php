<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EntrepriseCreatedMail;
use App\Models\ActivitySector;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EntrepriseController extends Controller
{
    public function index()
    {
        $entreprises = Entreprise::with(['user', 'activitySector'])->orderBy('nom')->get();
        return response()->json($entreprises);
    }

    public function referentiels()
    {
        return response()->json([
            'activity_sectors' => ActivitySector::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'   => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'logo'  => 'nullable|image|max:2048',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name'     => $request->nom,
            'email'    => $request->email,
            'password' => $password,
            'role'     => 'entreprise',
        ]);

        $data = [
            'user_id'            => $user->id,
            'nom'                => $request->nom,
            'description'        => $request->description,
            'site_web'           => $request->site_web,
            'telephone'          => $request->telephone,
            'adresse'            => $request->adresse,
            'ville'              => $request->ville,
            'pays'               => $request->pays,
            'activity_sector_id' => $request->activity_sector_id ?: null,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('entreprises/logos', 'public');
        }

        $entreprise = Entreprise::create($data);

        Mail::to($user->email)->send(new EntrepriseCreatedMail($request->nom, $user->email, $password));

        return response()->json($entreprise->load(['user', 'activitySector']), 201);
    }

    public function show(Entreprise $entreprise)
    {
        return response()->json($entreprise->load(['user', 'activitySector']));
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $request->validate([
            'nom'   => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $entreprise->user_id,
            'logo'  => 'nullable|image|max:2048',
        ]);

        $data = [
            'nom'                => $request->nom,
            'description'        => $request->description,
            'site_web'           => $request->site_web,
            'telephone'          => $request->telephone,
            'adresse'            => $request->adresse,
            'ville'              => $request->ville,
            'pays'               => $request->pays,
            'activity_sector_id' => $request->activity_sector_id ?: null,
        ];

        if ($request->hasFile('logo')) {
            if ($entreprise->logo) {
                Storage::disk('public')->delete($entreprise->logo);
            }
            $data['logo'] = $request->file('logo')->store('entreprises/logos', 'public');
        }

        $entreprise->update($data);

        if ($entreprise->user) {
            $entreprise->user->update([
                'name'  => $request->nom,
                'email' => $request->email,
            ]);
        }

        return response()->json($entreprise->fresh()->load(['user', 'activitySector']));
    }

    public function destroy(Entreprise $entreprise)
    {
        if ($entreprise->logo) {
            Storage::disk('public')->delete($entreprise->logo);
        }
        if ($entreprise->user) {
            $entreprise->user->delete();
        }
        $entreprise->delete();

        return response()->json(null, 204);
    }
}
