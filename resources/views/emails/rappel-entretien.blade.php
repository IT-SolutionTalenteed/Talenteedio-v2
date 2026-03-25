<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Rappel entretien</title></head>
<body>
    <h2>⏰ Votre entretien commence dans 1 heure !</h2>

    <p>Bonjour {{ $entretien->talent->name }},</p>

    <p>N'oubliez pas votre entretien prévu aujourd'hui :</p>

    <ul>
        <li><strong>Entreprise :</strong> {{ $entretien->entreprise->nom }}</li>
        <li><strong>Événement :</strong> {{ $entretien->evenement->titre }}</li>
        <li><strong>Heure :</strong> {{ substr($entretien->heure_debut, 0, 5) }} – {{ substr($entretien->heure_fin, 0, 5) }}</li>
    </ul>

    <p>Bonne chance !</p>
    <p>L'équipe Talenteed</p>
</body>
</html>
