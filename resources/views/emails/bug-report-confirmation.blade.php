<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de votre rapport de bug</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #192bc2 0%, #2563eb 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .message {
            font-size: 15px;
            color: #475569;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .info-box h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #0c4a6e;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #0369a1;
        }
        .summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .summary-item {
            margin-bottom: 12px;
            font-size: 14px;
        }
        .summary-label {
            font-weight: 600;
            color: #334155;
        }
        .summary-value {
            color: #64748b;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #192bc2 0%, #2563eb 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">✅</div>
            <h1>Merci pour votre rapport !</h1>
        </div>
        
        <div class="content">
            <p class="message">
                Bonjour <strong>{{ $nom }}</strong>,
            </p>
            
            <p class="message">
                Nous avons bien reçu votre rapport de bug et nous vous en remercions. Votre contribution nous aide à améliorer continuellement la plateforme Talenteedio.
            </p>

            <div class="info-box">
                <h3>📋 Que se passe-t-il maintenant ?</h3>
                <p>
                    Notre équipe technique va examiner votre rapport dans les plus brefs délais. 
                    Si nous avons besoin d'informations complémentaires, nous vous contacterons à l'adresse <strong>{{ $email }}</strong>.
                </p>
            </div>

            <div class="summary">
                <h3 style="margin: 0 0 15px; font-size: 16px; color: #1e293b;">Récapitulatif de votre rapport</h3>
                
                <div class="summary-item">
                    <span class="summary-label">Type :</span>
                    <span class="summary-value">
                        @switch($type)
                            @case('affichage') Problème d'affichage @break
                            @case('fonctionnalite') Fonctionnalité défectueuse @break
                            @case('performance') Problème de performance @break
                            @case('securite') Problème de sécurité @break
                            @default Autre @break
                        @endswitch
                    </span>
                </div>

                @if($url)
                <div class="summary-item">
                    <span class="summary-label">Page concernée :</span>
                    <span class="summary-value">{{ $url }}</span>
                </div>
                @endif

                <div class="summary-item">
                    <span class="summary-label">Date :</span>
                    <span class="summary-value">{{ now()->format('d/m/Y à H:i') }}</span>
                </div>
            </div>

            <p class="message">
                En attendant, n'hésitez pas à continuer d'utiliser la plateforme. Si vous rencontrez d'autres problèmes, vous pouvez nous les signaler à tout moment.
            </p>

            <center>
                <a href="{{ env('FRONTEND_URL', 'https://talenteedio.com') }}" class="cta-button">
                    Retourner sur Talenteedio
                </a>
            </center>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px;">
                <strong>Talenteedio</strong> - Plateforme de mobilisation des talents
            </p>
            <p style="margin: 0;">
                Besoin d'aide ? <a href="mailto:{{ env('MAIL_SUPPORT', 'support@talenteedio.com') }}">Contactez notre support</a>
            </p>
        </div>
    </div>
</body>
</html>
