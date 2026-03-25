<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Votre feedback</title></head>
<body>
    <h2>Comment s'est passé votre entretien ?</h2>

    <p>Bonjour {{ $entretien->talent->name }},</p>

    <p>
        Votre entretien avec <strong>{{ $entretien->entreprise->nom }}</strong>
        lors de <strong>{{ $entretien->evenement->titre }}</strong> vient de se terminer.
    </p>

    <p>Votre avis nous aide à améliorer l'expérience pour tous les talents.
    Prenez 1 minute pour laisser un feedback sur votre espace Talenteed.</p>

    <p>L'équipe Talenteed</p>
</body>
</html>
