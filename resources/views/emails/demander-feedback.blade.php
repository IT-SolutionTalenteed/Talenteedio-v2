@extends('emails.layout')

@section('title', 'Comment s\'est passé votre entretien ?')
@section('header_badge', 'Feedback entretien')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>
    </div>
    <div class="email-hero-title">Comment s'est passé votre entretien ?</div>
    <div class="email-hero-subtitle">Votre avis aide toute la communauté Talenteed.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $entretien->talent->name }},</p>
  <p class="email-text">
    Votre entretien avec <strong style="color:#040a5d;">{{ $entretien->entreprise->nom }}</strong> lors de <strong style="color:#040a5d;">{{ $entretien->evenement->titre }}</strong> vient de se terminer.
  </p>
  <p class="email-text">
    Prenez <strong>1 minute</strong> pour laisser un feedback sur votre expérience. Votre avis nous aide à améliorer la plateforme et à mieux préparer les prochains talents.
  </p>

  <div style="background: #f8faff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 20px 24px; margin: 24px 0; text-align: center;">
    <div style="font-size: 13px; color: #64748b; margin-bottom: 12px;">Notez votre expérience</div>
    <div style="font-size: 32px; letter-spacing: 6px; color: #f29f1f;">★★★★★</div>
    <div style="font-size: 12px; color: #94a3b8; margin-top: 8px;">Cliquez sur le bouton ci-dessous pour accéder au formulaire</div>
  </div>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">Laisser mon feedback</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    Merci pour votre confiance — L'équipe Talenteed
  </p>
@endsection
