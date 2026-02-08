{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" 
                                       required autocomplete="name" autofocus
                                       pattern="[a-zA-Z\s]+" 
                                       title="Only letters and spaces allowed">

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" 
                                       required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required 
                                       autocomplete="new-password"
                                       onkeyup="checkPasswordStrength(this.value)">

                                <div class="mt-2">
                                    <div class="progress" style="height: 10px;">
                                        <div id="password-strength-bar" 
                                             class="progress-bar" 
                                             role="progressbar" 
                                             style="width: 0%"></div>
                                    </div>
                                    <small id="password-strength-text" class="form-text text-muted"></small>
                                    <ul id="password-requirements" class="small mt-2">
                                        <li id="req-length">✓ Minimum 8 characters</li>
                                        <li id="req-uppercase">✓ One uppercase letter</li>
                                        <li id="req-lowercase">✓ One lowercase letter</li>
                                        <li id="req-number">✓ One number</li>
                                        <li id="req-special">✓ One special character</li>
                                    </ul>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" 
                                       class="form-control" 
                                       name="password_confirmation" 
                                       required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkPasswordStrength(password) {
    let strength = 0;
    const requirements = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };

    // Update requirement indicators
    document.getElementById('req-length').className = requirements.length ? 'text-success' : 'text-danger';
    document.getElementById('req-uppercase').className = requirements.upper ? 'text-success' : 'text-danger';
    document.getElementById('req-lowercase').className = requirements.lower ? 'text-success' : 'text-danger';
    document.getElementById('req-number').className = requirements.number ? 'text-success' : 'text-danger';
    document.getElementById('req-special').className = requirements.special ? 'text-success' : 'text-danger';

    // Calculate strength
    if (requirements.length) strength += 20;
    if (requirements.upper) strength += 20;
    if (requirements.lower) strength += 20;
    if (requirements.number) strength += 20;
    if (requirements.special) strength += 20;
    if (password.length >= 12) strength += 10;

    // Update progress bar
    const bar = document.getElementById('password-strength-bar');
    const text = document.getElementById('password-strength-text');
    
    bar.style.width = strength + '%';
    
    if (strength < 40) {
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Weak password';
    } else if (strength < 70) {
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'Moderate password';
    } else if (strength < 90) {
        bar.className = 'progress-bar bg-info';
        text.textContent = 'Good password';
    } else {
        bar.className = 'progress-bar bg-success';
        text.textContent = 'Strong password';
    }
}
</script>
@endsection