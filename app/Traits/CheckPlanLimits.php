<?php

namespace App\Traits;

use App\Models\Entreprise;

trait CheckPlanLimits
{
    private function getPlanLimit(Entreprise $entreprise, string $field): ?int
    {
        return $entreprise->plan?->$field ?? null;
    }

    protected function checkOffreLimit(Entreprise $entreprise): void
    {
        $max = $this->getPlanLimit($entreprise, 'max_offres');
        if ($max === null) return;

        if ($entreprise->offres()->count() >= $max) {
            abort(403, "Votre plan ne permet pas de publier plus de {$max} offre(s).");
        }
    }

    protected function checkArticleLimit(Entreprise $entreprise): void
    {
        $max = $this->getPlanLimit($entreprise, 'max_articles');
        if ($max === null) return;

        if ($entreprise->articles()->count() >= $max) {
            abort(403, "Votre plan ne permet pas de publier plus de {$max} article(s).");
        }
    }

    protected function checkEvenementLimit(Entreprise $entreprise): void
    {
        $max = $this->getPlanLimit($entreprise, 'max_evenements');
        if ($max === null) return;

        if ($entreprise->evenements()->count() >= $max) {
            abort(403, "Votre plan ne permet pas de participer à plus de {$max} événement(s).");
        }
    }

    protected function checkEntretienLimit(Entreprise $entreprise, int $evenementId): void
    {
        $max = $this->getPlanLimit($entreprise, 'max_entretiens_par_evenement');
        if ($max === null) return;

        $count = \App\Models\Entretien::where('entreprise_id', $entreprise->id)
            ->where('evenement_id', $evenementId)
            ->whereIn('statut', ['en_attente', 'confirme'])
            ->count();

        if ($count >= $max) {
            abort(403, "Votre plan limite à {$max} entretien(s) par événement sur votre stand.");
        }
    }
}
