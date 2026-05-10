@extends('emails.layout')

@section('title', __('emails.entreprise_inscription_admin.title'))
@section('header_badge', __('emails.entreprise_inscription_admin.badge'))

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(25,43,194,0.15); border-color:rgba(25,43,194,0.35);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#192bc2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
    </div>
    <div class="email-hero-title">{{ __('emails.entreprise_inscription_admin.hero_title') }}</div>
    <div class="email-hero-subtitle">{{ __('emails.entreprise_inscription_admin.hero_subtitle') }}</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.entreprise_inscription_admin.greeting') }}</p>

  <p class="email-text">
    {!! __('emails.entreprise_inscription_admin.intro') !!}
  </p>

  <div class="info-card" style="border-left-color: #192bc2;">
    <div class="info-card-title" style="color:#040a5d;">{{ __('emails.entreprise_inscription_admin.info_title') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_inscription_admin.info_company') }}</span>
      <span class="info-value">{{ $nomEntreprise }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_inscription_admin.info_email') }}</span>
      <span class="info-value">{{ $entrepriseUser->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_inscription_admin.info_date') }}</span>
      <span class="info-value">{{ now()->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_inscription_admin.info_status') }}</span>
      <span class="info-value">
        <span class="status-badge status-badge--pending">{{ __('emails.entreprise_inscription_admin.info_status_value') }}</span>
      </span>
    </div>
  </div>

  <p class="email-text">
    {{ __('emails.entreprise_inscription_admin.next_steps') }}
  </p>

  <div class="email-cta">
    <a href="{{ $adminUrl }}" class="btn-primary">
      {{ __('emails.entreprise_inscription_admin.cta') }}
    </a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    {{ __('emails.entreprise_inscription_admin.footer') }}
  </p>
@endsection
