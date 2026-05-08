<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau rapport de bug</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        .info-section {
            margin-bottom: 25px;
        }
        .info-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 15px;
            color: #1e293b;
            padding: 12px;
            background: #f8fafc;
            border-radius: 6px;
            border-left: 3px solid #ef4444;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-affichage { background: #dbeafe; color: #1e40af; }
        .badge-fonctionnalite { background: #fef3c7; color: #92400e; }
        .badge-performance { background: #fce7f3; color: #9f1239; }
        .badge-securite { background: #fee2e2; color: #991b1b; }
        .badge-autre { background: #e5e7eb; color: #374151; }
        .description-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-top: 8px;
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.7;
        }
        .footer {
            background: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🐛</div>
            <h1>Nouveau rapport de bug</h1>
        </div>
        
        <div class="content">
            <div class="info-section">
                <div class="info-label">Type de bug</div>
                <div class="info-value">
                    <span class="badge badge-{{ $type }}">
                        @switch($type)
                            @case('affichage') Problème d'affichage @break
                            @case('fonctionnalite') Fonctionnalité défectueuse @break
                            @case('performance') Problème de performance @break
                            @case('securite') Problème de sécurité @break
                            @default Autre @break
                        @endswitch
                    </span>
                </div>
            </div>

            <div class="info-section">
                <div class="info-label">Signalé par</div>
                <div class="info-value">
                    <strong>{{ $nom }}</strong><br>
                    📧 {{ $email }}
                </div>
            </div>

            @if($url)
            <div class="info-section">
                <div class="info-label">URL de la page</div>
                <div class="info-value">
                    <a href="{{ $url }}" style="color: #2563eb; text-decoration: none;">{{ $url }}</a>
                </div>
            </div>
            @endif

            @if($navigateur)
            <div class="info-section">
                <div class="info-label">Navigateur</div>
                <div class="info-value">{{ $navigateur }}</div>
            </div>
            @endif

            <div class="divider"></div>

            <div class="info-section">
                <div class="info-label">Description du problème</div>
                <div class="description-box">{{ $description }}</div>
            </div>

            @if($etapes)
            <div class="info-section">
                <div class="info-label">Étapes pour reproduire</div>
                <div class="description-box">{{ $etapes }}</div>
            </div>
            @endif
        </div>

        <div class="footer">
            <p style="margin: 0;">
                Ce rapport a été envoyé depuis la plateforme Talenteedio<br>
                <small>{{ now()->format('d/m/Y à H:i') }}</small>
            </p>
        </div>
    </div>
</body>
</html>
