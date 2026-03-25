<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Réservation d'entretien</title></head>
<body>
    @if($destinataire === 'talent')
        <h2>Votre entretien a bien été réservé !</h2>
        <p>Bonjour {{ $entretien->talent->name }},</p>
        <p>Votre demande d'entretien a été transmise à l'entreprise <strong>{{ $entretien->entreprise->nom }}</strong>.</p>
    @elseif($destinataire === 'entreprise')
        <h2>Nouvelle demande d'entretien sur votre stand</h2>
        <p>Bonjour,</p>
        <p>Le talent <strong>{{ $entretien->talent->name }}</strong> souhaite vous rencontrer lors de l'événement.</p>
    @else
        <h2>Nouvelle réservation d'entretien</h2>
        <p>Le talent <strong>{{ $entretien->talent->name }}</strong> a réservé un entretien avec <strong>{{ $entretien->entreprise->nom }}</strong>.</p>
    @endif

    <ul>
        <li><strong>Événement :</strong> {{ $entretien->evenement->titre }}</li>
        <li><strong>Date :</strong> {{ \Carbon\Carbon::parse($entretien->date)->format('d/m/Y') }}</li>
        <li><strong>Heure :</strong> {{ substr($entretien->heure_debut, 0, 5) }} – {{ substr($entretien->heure_fin, 0, 5) }}</li>
        <li><strong>Statut :</strong> En attente de confirmation</li>
    </ul>

    <p>L'équipe Talenteed</p>
</body>
</html>
