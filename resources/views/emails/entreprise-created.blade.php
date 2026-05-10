@extends('emails.layout')

@section('title', __('emails.entreprise_created.title'))
@section('header_badge', __('emails.entreprise_created.badge'))

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
      </svg>
    </div>
    <div class="email-hero-title">{{ __('emails.entreprise_created.hero_title', ['company' => $nomEntreprise]) }}</div>
    <div class="email-hero-subtitle">{{ __('emails.entreprise_created.hero_subtitle') }}</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.entreprise_created.greeting') }}</p>
  <p class="email-text">
    {!! __('emails.entreprise_created.intro', ['company' => $nomEntreprise]) !!}
  </p>

  <div class="info-card">
    <div class="info-card-title">{{ __('emails.entreprise_created.info_title') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_created.info_email') }}</span>
      <span class="info-value">{{ $email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_created.info_password') }}</span>
      <span class="info-value" style="font-family: monospace; font-size: 15px; letter-spacing: 1px; color: #040a5d; font-weight: 700;">{{ $password }}</span>
    </div>
  </div>

  <div class="highlight-box">
    <div class="highlight-box-label">{{ __('emails.entreprise_created.security_label') }}</div>
    <div style="font-size: 14px; color: #475569; margin-top: 4px;">
      {!! __('emails.entreprise_created.security_text') !!}
    </div>
  </div>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">{{ __('emails.entreprise_created.cta') }}</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    {{ __('emails.entreprise_created.contact') }}
  </p>
@endsection
