<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Demande de participation à un événement</title>
</head>
<body>
    <h2>Nouvelle demande de participation</h2>

    <p>L'entreprise <strong>{{ $nomEntreprise }}</strong> souhaite participer à l'événement <strong>{{ $nomEvenement }}</strong>.</p>

    @if($message)
    <p><strong>Message :</strong> {{ $message }}</p>
    @endif

    <p>Connectez-vous à l'espace administrateur pour accepter ou refuser cette demande.</p>

    <p>L'équipe Talenteed</p>
</body>
</html>
