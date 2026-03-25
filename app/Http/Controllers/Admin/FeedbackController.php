<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    /**
     * D-06 — Liste de tous les feedbacks.
     */
    public function index()
    {
        $feedbacks = Feedback::with([
            'talent',
            'entretien.entreprise',
            'entretien.evenement',
        ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($feedbacks);
    }
}
