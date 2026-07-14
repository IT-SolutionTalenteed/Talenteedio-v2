<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObservatorySubmission;

class ObservatorySubmissionController extends Controller
{
    /**
     * Liste toutes les soumissions Africa Talent Observatory (lecture seule).
     */
    public function index()
    {
        return response()->json(
            ObservatorySubmission::orderBy('created_at', 'desc')->get()
        );
    }
}
