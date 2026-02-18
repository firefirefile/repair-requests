@extends('layouts.guest')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror"
               id="email" name="email" value="{{ old('email', 'dispatcher@example.com') }}" required autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Пароль</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror"
               id="password" name="password" value="password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">Запомнить меня</label>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Войти</button>
    </div>
</form>

<div class="mt-3 text-center">
    <p class="text-muted mb-1">Тестовые пользователи:</p>
    <p class="small mb-0">Диспетчер: dispatcher@example.com / password</p>
    <p class="small mb-0">Мастер: Ivan@example.com / password</p>
    <p class="small">Мастер: Petr@example.com / password</p>
</div>
@endsection
