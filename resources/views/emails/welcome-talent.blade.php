@extends('emails.layout')

@section('title', __('emails.welcome_talent.title'))
@section('header_badge', __('emails.welcome_talent.badge'))

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
      </svg>
    </div>
    <div class="email-hero-title">{{ __('emails.welcome_talent.hero_title', ['name' => $user->name]) }}</div>
    <div class="email-hero-subtitle">{{ __('emails.welcome_talent.hero_subtitle') }}</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.welcome_talent.greeting', ['name' => $user->name]) }}</p>
  <p class="email-text">
    {!! __('emails.welcome_talent.intro') !!}
  </p>

  <p class="email-text" style="margin-bottom: 8px; font-weight: 600; color: #040a5d;">{{ __('emails.welcome_talent.features_title') }}</p>
  <ul class="email-list">
    <li>{{ __('emails.welcome_talent.feature_1') }}</li>
    <li>{{ __('emails.welcome_talent.feature_2') }}</li>
    <li>{!! __('emails.welcome_talent.feature_3') !!}</li>
    <li>{{ __('emails.welcome_talent.feature_4') }}</li>
    <li>{{ __('emails.welcome_talent.feature_5') }}</li>
  </ul>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">{{ __('emails.welcome_talent.cta') }}</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    {{ __('emails.welcome_talent.contact') }}
  </p>
@endsection
