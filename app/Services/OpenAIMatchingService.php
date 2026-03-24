<?php

namespace App\Services;

use App\Models\Evenement;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIMatchingService
{
    /**
     * Génère un matching entre le profil du talent et les entreprises de l'événement.
     *
     * @param  string       $talentProfile  Description du profil talent (nom, poste recherché, compétences)
     * @param  string|null  $cvText         Contenu textuel du CV (optionnel)
     * @param  Evenement    $evenement      Événement avec ses entreprises + offres chargées
     * @return array        Liste triée d'entreprises avec score et explication
     */
    public function match(string $talentProfile, ?string $cvText, Evenement $evenement): array
    {
        $evenement->loadMissing(['entreprises.offres']);

        $entreprisesList = $evenement->entreprises->map(function ($e) {
            $offres = $e->offres->map(fn($o) => "- {$o->titre}" . ($o->description ? " : {$o->description}" : ''))->join("\n");
            return "Entreprise: {$e->nom}" . ($e->description ? " — {$e->description}" : '') . "\nOffres:\n{$offres}";
        })->join("\n\n");

        if ($evenement->entreprises->isEmpty()) {
            return [];
        }

        $systemPrompt = <<<PROMPT
Tu es un assistant de recrutement. On te donne le profil d'un talent et la liste des entreprises participantes à un événement de recrutement avec leurs offres d'emploi.
Tu dois classer les entreprises du meilleur match au moins bon match pour ce talent.
Réponds UNIQUEMENT avec un JSON valide, sans texte autour, sous cette forme :
[
  {"entreprise_id": 1, "nom": "...", "score": 85, "raison": "..."},
  ...
]
Les scores vont de 0 à 100. Classe toutes les entreprises.
PROMPT;

        $userContent = "PROFIL DU TALENT :\n{$talentProfile}";
        if ($cvText) {
            $userContent .= "\n\nCV :\n{$cvText}";
        }
        $userContent .= "\n\nENTREPRISES DE L'ÉVÉNEMENT :\n{$entreprisesList}";

        // Ajouter les IDs réels dans les données envoyées à OpenAI
        $entreprisesData = $evenement->entreprises->map(fn($e) => [
            'id'  => $e->id,
            'nom' => $e->nom,
        ])->values()->toArray();

        $userContent .= "\n\nIDs des entreprises (utilise ces IDs dans ta réponse) :\n";
        foreach ($entreprisesData as $e) {
            $userContent .= "- entreprise_id={$e['id']}, nom={$e['nom']}\n";
        }

        $response = OpenAI::chat()->create([
            'model'    => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userContent],
            ],
            'max_tokens'  => 1000,
            'temperature' => 0.3,
        ]);

        $content = $response->choices[0]->message->content ?? '[]';

        // Nettoyer la réponse au cas où OpenAI ajoute du markdown
        $content = preg_replace('/^```json\s*/m', '', $content);
        $content = preg_replace('/^```\s*/m', '', $content);
        $content = trim($content);

        $results = json_decode($content, true) ?? [];

        // Réenrichir avec les données locales complètes
        return collect($results)->map(function ($item) use ($evenement) {
            $entreprise = $evenement->entreprises->firstWhere('id', $item['entreprise_id']);
            if ($entreprise) {
                $item['logo_url'] = $entreprise->logo_url;
                $item['description'] = $entreprise->description;
                $item['ville'] = $entreprise->ville;
            }
            return $item;
        })->sortByDesc('score')->values()->toArray();
    }
}
