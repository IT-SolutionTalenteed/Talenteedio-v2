<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class BugReportController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'type' => 'required|string|in:affichage,fonctionnalite,performance,securite,autre',
            'url' => 'nullable|url|max:500',
            'navigateur' => 'nullable|string|max:255',
            'description' => 'required|string|min:10',
            'etapes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Envoyer l'email à l'équipe technique
        try {
            Mail::send('emails.bug-report', $data, function ($message) use ($data) {
                $message->to(env('MAIL_SUPPORT', 'support@talenteedio.com'))
                    ->subject('🐛 Nouveau rapport de bug - ' . $data['type'])
                    ->replyTo($data['email'], $data['nom']);
            });

            // Envoyer un email de confirmation à l'utilisateur
            Mail::send('emails.bug-report-confirmation', $data, function ($message) use ($data) {
                $message->to($data['email'], $data['nom'])
                    ->subject('Confirmation de votre rapport de bug - Talenteedio');
            });

            return response()->json([
                'message' => 'Rapport de bug envoyé avec succès'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email bug report: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erreur lors de l\'envoi du rapport'
            ], 500);
        }
    }
}
