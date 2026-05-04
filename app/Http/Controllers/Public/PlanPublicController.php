<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanPublicController extends Controller
{
    public function index()
    {
        return response()->json(
            Plan::where('is_active', true)->orderBy('price')->get()
        );
    }
}
