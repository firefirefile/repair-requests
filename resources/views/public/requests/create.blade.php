@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Создать заявку в ремонтную службу</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('requests.store') }}">
        @csrf

        <div class="form-group">
            <label for="client_name">Имя клиента *</label>
            <input type="text" name="client_name" id="client_name"
                   class="form-control @error('client_name') is-invalid @enderror"
                   value="{{ old('client_name') }}" required>
            @error('client_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">Телефон *</label>
            <input type="text" name="phone" id="phone"
                   class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone') }}" required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="address">Адрес *</label>
            <input type="text" name="address" id="address"
                   class="form-control @error('address') is-invalid @enderror"
                   value="{{ old('address') }}" required>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="problem_text">Описание проблемы *</label>
            <textarea name="problem_text" id="problem_text"
                      class="form-control @error('problem_text') is-invalid @enderror"
                      rows="5" required>{{ old('problem_text') }}</textarea>
            @error('problem_text')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Отправить заявку</button>
    </form>
</div>
@endsection
