@extends('emails.layout')

@section('title', __('emails.entreprise_activated.title'))
@section('header_badge', __('emails.entreprise_activated.badge'))

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(34,197,94,0.2); border-color:rgba(34,197,94,0.4);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    </div>
    <div class="email-hero-title">{{ __('emails.entreprise_activated.hero_title') }}</div>
    <div class="email-hero-subtitle">{{ __('emails.entreprise_activated.hero_subtitle') }}</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.entreprise_activated.greeting', ['name' => $user->name]) }}</p>

  <p class="email-text">
    {!! __('emails.entreprise_activated.intro') !!}
  </p>

  <div class="info-card" style="border-left-color: #22c55e;">
    <div class="info-card-title" style="color:#15803d;">{{ __('emails.entreprise_activated.info_title') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_activated.info_account') }}</span>
      <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_activated.info_status') }}</span>
      <span class="info-value">
        <span class="status-badge status-badge--success">{{ __('emails.entreprise_activated.info_status_value') }}</span>
      </span>
    </div>
  </div>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/login" class="btn-primary">
      {{ __('emails.entreprise_activated.cta') }}
    </a>
  </div>

  <p class="email-text" style="font-weight:600; color:#040a5d;">{{ __('emails.entreprise_activated.features_title') }}</p>
  <ul class="email-list">
    <li>{{ __('emails.entreprise_activated.feature_1') }}</li>
    <li>{{ __('emails.entreprise_activated.feature_2') }}</li>
    <li>{!! __('emails.entreprise_activated.feature_3') !!}</li>
    <li>{{ __('emails.entreprise_activated.feature_4') }}</li>
  </ul>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    {{ __('emails.entreprise_activated.contact') }}
  </p>
@endsection
