<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriController extends Controller
{
    public function index()
    {
        $favoris = auth()->user()
            ->favorisOffres()
            ->with(['entreprise', 'jobContracts', 'jobModes'])
            ->get();

        return response()->json($favoris);
    }

    public function toggle(Offre $offre)
    {
        $talentId = auth()->id();

        $exists = DB::table('offre_favori')
            ->where('talent_id', $talentId)
            ->where('offre_id', $offre->id)
            ->exists();

        if ($exists) {
            DB::table('offre_favori')
                ->where('talent_id', $talentId)
                ->where('offre_id', $offre->id)
                ->delete();

            return response()->json(['favori' => false]);
        }

        DB::table('offre_favori')->insert([
            'talent_id' => $talentId,
            'offre_id'  => $offre->id,
        ]);

        return response()->json(['favori' => true]);
    }
}
