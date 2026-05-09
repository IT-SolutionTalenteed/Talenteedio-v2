<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "=== TEST D'ENVOI EMAIL SIGNALEMENT DE BUG ===\n\n";

// Données de test
$data = [
    'nom' => 'Jean Dupont',
    'email' => 'jean.dupont@example.com',
    'type' => 'fonctionnalite',
    'url' => 'http://localhost:5173/evenements/categorie/2',
    'navigateur' => 'Chrome 120.0.0 (Windows 10)',
    'description' => 'Les balises HTML sont visibles dans les descriptions des événements. Au lieu de voir le texte formaté, je vois les balises <p>, <strong>, etc.',
    'etapes' => "1. Aller sur la page http://localhost:5173/evenements/categorie/2\n2. Scroller jusqu'à la section 'À propos de cet événement'\n3. Observer que les balises HTML sont affichées en texte brut"
];

echo "📧 Envoi de l'email à l'équipe technique...\n";
echo "Destinataire: " . env('MAIL_SUPPORT', 'support@talenteedio.com') . "\n\n";

try {
    // Email à l'équipe technique
    Mail::send('emails.bug-report', $data, function ($message) use ($data) {
        $message->to(env('MAIL_SUPPORT', 'support@talenteedio.com'))
            ->subject('🐛 Nouveau rapport de bug - ' . $data['type'])
            ->replyTo($data['email'], $data['nom']);
    });
    
    echo "✅ Email envoyé à l'équipe technique avec succès!\n\n";
    
    // Email de confirmation à l'utilisateur
    echo "📧 Envoi de l'email de confirmation à l'utilisateur...\n";
    echo "Destinataire: " . $data['email'] . "\n\n";
    
    Mail::send('emails.bug-report-confirmation', $data, function ($message) use ($data) {
        $message->to($data['email'], $data['nom'])
            ->subject('Confirmation de votre rapport de bug - Talenteedio');
    });
    
    echo "✅ Email de confirmation envoyé avec succès!\n\n";
    
    echo "=== TEST TERMINÉ AVEC SUCCÈS ===\n";
    echo "\n📋 Résumé:\n";
    echo "- Email équipe: ✅ Envoyé\n";
    echo "- Email confirmation: ✅ Envoyé\n";
    echo "\n💡 Vérifiez votre boîte mail (ou les logs si en mode local)\n";
    
} catch (\Exception $e) {
    echo "❌ ERREUR lors de l'envoi: " . $e->getMessage() . "\n";
    echo "\n📝 Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    
    echo "\n\n💡 Vérifications à faire:\n";
    echo "1. Vérifiez la configuration MAIL dans .env\n";
    echo "2. Si vous utilisez Mailtrap/MailHog, vérifiez qu'il est démarré\n";
    echo "3. Vérifiez les logs: storage/logs/laravel.log\n";
}

echo "\n";
