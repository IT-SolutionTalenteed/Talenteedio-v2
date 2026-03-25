<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Réponse à votre entretien</title></head>
<body>
    <h2>
        @if($entretien->statut === 'confirme')
            ✅ Votre entretien a été confirmé !
        @else
            ❌ Votre entretien a été refusé
        @endif
    </h2>

    <p>Bonjour {{ $entretien->talent->name }},</p>

    <p>
        @if($entretien->statut === 'confirme')
            L'entreprise <strong>{{ $entretien->entreprise->nom }}</strong> a confirmé votre entretien.
        @else
            L'entreprise <strong>{{ $entretien->entreprise->nom }}</strong> n'est pas disponible à ce créneau.
        @endif
    </p>

    <ul>
        <li><strong>Événement :</strong> {{ $entretien->evenement->titre }}</li>
        <li><strong>Date :</strong> {{ \Carbon\Carbon::parse($entretien->date)->format('d/m/Y') }}</li>
        <li><strong>Heure :</strong> {{ substr($entretien->heure_debut, 0, 5) }} – {{ substr($entretien->heure_fin, 0, 5) }}</li>
    </ul>

    <p>L'équipe Talenteed</p>
</body>
</html>
