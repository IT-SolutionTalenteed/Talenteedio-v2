<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EntrepriseCreatedMail;
use App\Models\ActivitySector;
use App\Models\Entreprise;
use App\Models\Plan;
use App\Models\User;
use App\Services\CompressedImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EntrepriseController extends Controller
{
    public function __construct(
        private CompressedImageStorage $compressedImages,
    ) {}

    public function index(Request $request)
    {
        $query = Entreprise::with(['user', 'activitySector', 'plan'])->orderBy('nom');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    public function referentiels()
    {
        return response()->json([
            'activity_sectors' => ActivitySector::orderBy('name')->get(),
            'plans' => Plan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'logo' => 'nullable|image|max:2048',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->nom,
            'email' => $request->email,
            'password' => $password,
            'role' => 'entreprise',
            'status' => 'active',
        ]);

        $data = [
            'user_id' => $user->id,
            'nom' => $request->nom,
            'status' => 'active',
            'taille' => $request->taille,
            'poste_contact' => $request->poste_contact,
            'description' => $request->description,
            'site_web' => $request->site_web,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'pays' => $request->pays,
            'activity_sector_id' => $request->activity_sector_id ?: null,
            'plan_id' => $request->plan_id ?: null,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->compressedImages->store(
                $request->file('logo'),
                'entreprises/logos',
                'logo'
            );
        }

        $entreprise = Entreprise::create($data);

        Mail::to($user->email)->send(new EntrepriseCreatedMail($request->nom, $user->email, $password));

        return response()->json($entreprise->load(['user', 'activitySector', 'plan']), 201);
    }

    public function show(Entreprise $entreprise)
    {
        return response()->json($entreprise->load(['user', 'activitySector', 'plan']));
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$entreprise->user_id,
            'logo' => 'nullable|image|max:2048',
            'status' => 'nullable|string|in:active,pending,suspended',
        ]);

        $data = [
            'nom' => $request->nom,
            'status' => $request->status ?? $entreprise->status,
            'taille' => $request->taille,
            'poste_contact' => $request->poste_contact,
            'description' => $request->description,
            'site_web' => $request->site_web,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'pays' => $request->pays,
            'activity_sector_id' => $request->activity_sector_id ?: null,
            'plan_id' => $request->plan_id ?: null,
        ];

        if ($request->hasFile('logo')) {
            if ($entreprise->logo) {
                Storage::disk('public')->delete($entreprise->logo);
            }
            $data['logo'] = $this->compressedImages->store(
                $request->file('logo'),
                'entreprises/logos',
                'logo'
            );
        }

        $entreprise->update($data);

        if ($entreprise->user) {
            $userData = [
                'name' => $request->nom,
                'email' => $request->email,
            ];
            if ($request->has('status')) {
                $userData['is_suspended'] = $request->status === 'suspended';
            }
            $entreprise->user->update($userData);
        }

        return response()->json($entreprise->fresh()->load(['user', 'activitySector', 'plan']));
    }

    public function updateStatus(Request $request, Entreprise $entreprise)
    {
        $request->validate([
            'status' => 'required|string|in:active,pending,suspended',
        ]);

        $entreprise->update(['status' => $request->status]);

        if ($entreprise->user) {
            $entreprise->user->update(['is_suspended' => $request->status === 'suspended']);
        }

        return response()->json($entreprise->fresh()->load(['user', 'activitySector', 'plan']));
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
