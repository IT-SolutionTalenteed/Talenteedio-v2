<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $url }}">
    <link rel="icon" href="{{ config('frontend.url') }}/favicon.png" type="image/png">

    {{-- Open Graph : Facebook, WhatsApp, LinkedIn, Messenger, Slack... --}}
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="Talenteedio">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:secure_url" content="{{ $image }}">
    <meta property="og:image:alt" content="{{ $title }}">

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $url }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">
</head>
<body>
    {{-- Cette page n'est servie qu'aux robots des réseaux sociaux (voir nginx.conf).
         Le contenu ci-dessous sert de secours si un humain y arrive malgré tout. --}}
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    <p><a href="{{ $url }}">Voir sur Talenteedio</a></p>
</body>
</html>
