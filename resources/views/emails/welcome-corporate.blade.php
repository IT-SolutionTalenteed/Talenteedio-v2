@extends('emails.layout')

@section('title', __('emails.welcome_corporate.title'))
@section('header_badge', __('emails.welcome_corporate.badge'))

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
      </svg>
    </div>
    <div class="email-hero-title">{{ __('emails.welcome_corporate.hero_title', ['name' => $user->entreprise->nom ?? $user->name]) }}</div>
    <div class="email-hero-subtitle">{{ __('emails.welcome_corporate.hero_subtitle') }}</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.welcome_corporate.greeting', ['name' => $user->name]) }}</p>
  <p class="email-text">
    {!! __('emails.welcome_corporate.intro') !!}
  </p>

  <p class="email-text" style="margin-bottom: 8px; font-weight: 600; color: #040a5d;">{{ __('emails.welcome_corporate.features_title') }}</p>
  <ul class="email-list">
    <li>{{ __('emails.welcome_corporate.feature_1') }}</li>
    <li>{{ __('emails.welcome_corporate.feature_2') }}</li>
    <li>{!! __('emails.welcome_corporate.feature_3') !!}</li>
    <li>{{ __('emails.welcome_corporate.feature_4') }}</li>
    <li>{{ __('emails.welcome_corporate.feature_5') }}</li>
  </ul>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">{{ __('emails.welcome_corporate.cta') }}</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    {{ __('emails.welcome_corporate.contact') }}
  </p>
@endsection
