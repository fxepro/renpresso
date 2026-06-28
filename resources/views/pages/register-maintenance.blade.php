@extends('layouts.auth', ['page' => 'register', 'registerPage' => true])

@section('title', 'Register maintenance team')
@section('meta_description', 'Register your team on Renpresso.')

@section('content')

<div class="rm-card">
  <h1>List your maintenance team</h1>
  <p class="rm-sub">
    @if($invite)
      <strong>{{ $invite->landlord->fullName() }}</strong> invited you. Complete your team profile — you will appear in their roster after signup.
    @else
      Register once and appear in the directory for your city. Landlords with properties there can add you to their team.
    @endif
  </p>

  @if(!empty($inviteError))
    <div class="rm-alert">{{ $inviteError }}</div>
  @endif

  <form class="rm-form" method="POST" action="{{ route('register.maintenance.store') }}">
    @csrf
    @if($invite_token)
      <input type="hidden" name="invite_token" value="{{ $invite_token }}">
    @endif

    <p class="rm-section">Your account</p>
    <div class="rm-row">
      <div class="rm-field">
        <label for="first_name">First name</label>
        <input id="first_name" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name">
        @error('first_name')<div class="rm-err">{{ $message }}</div>@enderror
      </div>
      <div class="rm-field">
        <label for="last_name">Last name</label>
        <input id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
        @error('last_name')<div class="rm-err">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="rm-field">
      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email', optional($invite)->email) }}" required autocomplete="email" @if($invite) readonly @endif>
      @error('email')<div class="rm-err">{{ $message }}</div>@enderror
    </div>
    <div class="rm-row">
      <div class="rm-field">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" minlength="8">
        @error('password')<div class="rm-err">{{ $message }}</div>@enderror
      </div>
      <div class="rm-field">
        <label for="password_confirmation">Confirm</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
      </div>
    </div>

    <p class="rm-section">Team listing</p>
    <div class="rm-field">
      <label for="team_name">Team / company name</label>
      <input id="team_name" name="team_name" value="{{ old('team_name') }}" required placeholder="e.g. Paris Property Care">
      @error('team_name')<div class="rm-err">{{ $message }}</div>@enderror
    </div>
    <div class="rm-row">
      <div class="rm-field">
        <label for="city">City</label>
        <input id="city" name="city" value="{{ old('city') }}" required placeholder="Paris">
        @error('city')<div class="rm-err">{{ $message }}</div>@enderror
      </div>
      <div class="rm-field">
        <label for="country_code">Country</label>
        <select id="country_code" name="country_code" required>
          <option value="">Select…</option>
          @foreach(array_keys(config('countries')) as $code)
            <option value="{{ $code }}" {{ old('country_code') === $code ? 'selected' : '' }}>{{ $code }}</option>
          @endforeach
        </select>
        @error('country_code')<div class="rm-err">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="rm-field">
      <label for="phone">Phone (optional)</label>
      <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
    </div>
    <div class="rm-field">
      <label for="description">About your team (optional)</label>
      <textarea id="description" name="description" placeholder="Services, response times, areas covered…">{{ old('description') }}</textarea>
      @error('description')<div class="rm-err">{{ $message }}</div>@enderror
    </div>

    @error('invite_token')<div class="rm-err">{{ $message }}</div>@enderror
    <button type="submit" class="rm-submit">List my team</button>
  </form>
  <p class="rm-foot">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</div>

@endsection
