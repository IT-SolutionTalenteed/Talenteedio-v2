<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title', 'Talenteed')</title>
  <style>
    /* ── Reset ── */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f0f4f8;
      color: #1e293b;
      -webkit-font-smoothing: antialiased;
    }
    a { color: inherit; text-decoration: none; }
    img { display: block; border: 0; }

    /* ── Wrapper ── */
    .email-wrapper {
      width: 100%;
      padding: 40px 16px;
      background: #f0f4f8;
    }
    .email-card {
      max-width: 600px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(4,10,93,0.10);
    }

    /* ── Header ── */
    .email-header {
      background: linear-gradient(135deg, #040a5d 0%, #192bc2 100%);
      padding: 32px 40px 28px;
      text-align: center;
    }
    .email-logo {
      display: inline-block;
      margin-bottom: 0;
    }
    .email-logo-text {
      font-size: 26px;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -0.5px;
    }
    .email-logo-text span {
      color: #f29f1f;
    }
    .email-header-badge {
      display: inline-block;
      margin-top: 12px;
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.25);
      border-radius: 50px;
      padding: 4px 14px;
      font-size: 12px;
      color: rgba(255,255,255,0.9);
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    /* ── Hero band (optionnel par template) ── */
    .email-hero-band {
      background: linear-gradient(135deg, #040a5d 0%, #192bc2 60%, #2687e9 100%);
      padding: 28px 40px;
      text-align: center;
    }
    .email-hero-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: rgba(255,255,255,0.15);
      border: 2px solid rgba(255,255,255,0.3);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
    }
    .email-hero-title {
      font-size: 22px;
      font-weight: 800;
      color: #ffffff;
      line-height: 1.3;
    }
    .email-hero-subtitle {
      font-size: 14px;
      color: rgba(255,255,255,0.8);
      margin-top: 6px;
      line-height: 1.5;
    }

    /* ── Body ── */
    .email-body {
      padding: 36px 40px;
    }
    .email-greeting {
      font-size: 16px;
      color: #1e293b;
      margin-bottom: 12px;
      font-weight: 600;
    }
    .email-text {
      font-size: 15px;
      color: #475569;
      line-height: 1.7;
      margin-bottom: 16px;
    }

    /* ── Info card ── */
    .info-card {
      background: #f8faff;
      border: 1.5px solid #e2e8f0;
      border-left: 4px solid #192bc2;
      border-radius: 10px;
      padding: 20px 24px;
      margin: 24px 0;
    }
    .info-card-title {
      font-size: 11px;
      font-weight: 700;
      color: #192bc2;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 12px;
    }
    .info-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 8px 0;
      border-bottom: 1px solid #e8edf5;
      font-size: 14px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
      font-weight: 700;
      color: #040a5d;
      min-width: 110px;
      flex-shrink: 0;
    }
    .info-value {
      color: #475569;
    }

    /* ── Status badge ── */
    .status-badge {
      display: inline-block;
      padding: 6px 16px;
      border-radius: 50px;
      font-size: 13px;
      font-weight: 700;
    }
    .status-badge--success {
      background: #dcfce7;
      color: #15803d;
    }
    .status-badge--warning {
      background: #fef9c3;
      color: #a16207;
    }
    .status-badge--danger {
      background: #fee2e2;
      color: #dc2626;
    }
    .status-badge--pending {
      background: #e0f2fe;
      color: #0369a1;
    }

    /* ── CTA Button ── */
    .email-cta {
      text-align: center;
      margin: 28px 0;
    }
    .btn-primary {
      display: inline-block;
      background: linear-gradient(135deg, #f29f1f, #f07c00);
      color: #ffffff !important;
      font-size: 15px;
      font-weight: 700;
      padding: 14px 36px;
      border-radius: 8px;
      text-decoration: none;
      letter-spacing: 0.2px;
    }
    .btn-secondary {
      display: inline-block;
      background: #040a5d;
      color: #ffffff !important;
      font-size: 15px;
      font-weight: 700;
      padding: 14px 36px;
      border-radius: 8px;
      text-decoration: none;
    }

    /* ── Highlight box ── */
    .highlight-box {
      background: linear-gradient(135deg, rgba(242,159,31,0.08), rgba(242,159,31,0.15));
      border: 1.5px solid rgba(242,159,31,0.35);
      border-radius: 10px;
      padding: 18px 22px;
      margin: 20px 0;
      text-align: center;
    }
    .highlight-box-label {
      font-size: 11px;
      font-weight: 700;
      color: #b45309;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 6px;
    }
    .highlight-box-value {
      font-size: 20px;
      font-weight: 800;
      color: #040a5d;
    }

    /* ── Divider ── */
    .email-divider {
      border: none;
      border-top: 1px solid #e8edf5;
      margin: 24px 0;
    }

    /* ── List ── */
    .email-list {
      list-style: none;
      margin: 16px 0;
    }
    .email-list li {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 14px;
      color: #475569;
      padding: 8px 0;
      line-height: 1.5;
    }
    .email-list li::before {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #192bc2;
      margin-top: 7px;
      flex-shrink: 0;
    }

    /* ── Footer ── */
    .email-footer {
      background: #040a5d;
      padding: 24px 40px;
      text-align: center;
    }
    .email-footer-logo {
      font-size: 16px;
      font-weight: 800;
      color: #ffffff;
      margin-bottom: 8px;
    }
    .email-footer-logo span { color: #f29f1f; }
    .email-footer-tagline {
      font-size: 12px;
      color: rgba(255,255,255,0.55);
      margin-bottom: 16px;
      line-height: 1.5;
    }
    .email-footer-links {
      margin-bottom: 16px;
    }
    .email-footer-links a {
      color: rgba(255,255,255,0.65);
      font-size: 12px;
      margin: 0 10px;
      text-decoration: none;
    }
    .email-footer-copy {
      font-size: 11px;
      color: rgba(255,255,255,0.35);
    }

    /* ── Responsive ── */
    @media (max-width: 600px) {
      .email-body { padding: 24px 20px; }
      .email-header { padding: 24px 20px 20px; }
      .email-hero-band { padding: 22px 20px; }
      .email-footer { padding: 20px; }
      .info-card { padding: 16px; }
      .info-label { min-width: 90px; }
      .btn-primary, .btn-secondary { padding: 12px 24px; font-size: 14px; }
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    <div class="email-card">

      <!-- Header -->
      <div class="email-header">
        <div class="email-logo">
          <div class="email-logo-text">Talent<span>eed</span></div>
        </div>
        @hasSection('header_badge')
          <div><div class="email-header-badge">@yield('header_badge')</div></div>
        @endif
      </div>

      <!-- Hero band -->
      @hasSection('hero')
        <div class="email-hero-band">
          @yield('hero')
        </div>
      @endif

      <!-- Body -->
      <div class="email-body">
        @yield('content')
      </div>

      <!-- Footer -->
      <div class="email-footer">
        <div class="email-footer-logo">Talent<span>eed</span></div>
        <div class="email-footer-tagline">La plateforme de recrutement événementiel<br>qui connecte les talents et les entreprises.</div>
        <div class="email-footer-links">
          <a href="https://talenteed.io">talenteed.io</a>
          <a href="https://talenteed.io/offres">Offres</a>
          <a href="https://talenteed.io/evenements">Événements</a>
        </div>
        <div class="email-footer-copy">&copy; {{ date('Y') }} Talenteed. Tous droits réservés.</div>
      </div>

    </div>
  </div>
</body>
</html>
