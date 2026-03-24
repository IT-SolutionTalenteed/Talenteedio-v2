<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vos identifiants Talenteed</title>
</head>
<body>
    <h2>Bienvenue sur Talenteed, {{ $nomEntreprise }} !</h2>

    <p>Un compte entreprise a été créé pour vous sur la plateforme Talenteed.</p>

    <p>Voici vos identifiants de connexion :</p>

    <ul>
        <li><strong>Email :</strong> {{ $email }}</li>
        <li><strong>Mot de passe :</strong> {{ $password }}</li>
    </ul>

    <p>Nous vous recommandons de changer votre mot de passe dès votre première connexion.</p>

    <p>L'équipe Talenteed</p>
</body>
</html>
