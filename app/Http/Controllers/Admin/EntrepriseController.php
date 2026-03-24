<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EntrepriseCreatedMail;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EntrepriseController extends Controller
{
    public function index()
    {
        $entreprises = Entreprise::with('user')->orderBy('nom')->get();
        return response()->json($entreprises);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'   => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name'     => $request->nom,
            'email'    => $request->email,
            'password' => $password,
            'role'     => 'entreprise',
        ]);

        $entreprise = Entreprise::create([
            'user_id' => $user->id,
            'nom'     => $request->nom,
        ]);

        Mail::to($user->email)->send(new EntrepriseCreatedMail($request->nom, $user->email, $password));

        return response()->json($entreprise->load('user'), 201);
    }

    public function show(Entreprise $entreprise)
    {
        return response()->json($entreprise->load('user'));
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $request->validate([
            'nom'   => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $entreprise->user_id,
        ]);

        $entreprise->update(['nom' => $request->nom]);

        if ($entreprise->user) {
            $entreprise->user->update([
                'name'  => $request->nom,
                'email' => $request->email,
            ]);
        }

        return response()->json($entreprise->load('user'));
    }

    public function destroy(Entreprise $entreprise)
    {
        if ($entreprise->user) {
            $entreprise->user->delete();
        }
        $entreprise->delete();

        return response()->json(null, 204);
    }
}
