@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Панель диспетчера</h1>

    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dispatcher.requests.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="status" class="form-label">Статус</label>
                    <select name="status" class="form-select" id="status">
                        <option value="all">Все статусы</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Новые</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Назначенные</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>В работе</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Выполненные</option>
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Отмененные</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="master" class="form-label">Мастер</label>
                    <select name="master" class="form-select" id="master">
                        <option value="">Все мастера</option>
                        @foreach($masters as $master)
                            <option value="{{ $master->id }}" {{ request('master') == $master->id ? 'selected' : '' }}>
                                {{ $master->name }} ({{ $master->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Фильтр</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Список заявок -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Список заявок</h5>
        </div>
        <div class="card-body">
            @if($requests->isEmpty())
                <p class="text-center text-muted">Заявок нет</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Клиент</th>
                                <th>Телефон</th>
                                <th>Адрес</th>
                                <th>Проблема</th>
                                <th>Статус</th>
                                <th>Мастер</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>{{ $request->client_name }}</td>
                                <td>{{ $request->phone }}</td>
                                <td>{{ $request->address }}</td>
                                <td>{{ Str::limit($request->problem_text, 30) }}</td>
                                <td>
                                    @switch($request->status)
                                        @case('new')
                                            <span class="badge bg-primary">Новая</span>
                                            @break
                                        @case('assigned')
                                            <span class="badge bg-warning">Назначена</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-info">В работе</span>
                                            @break
                                        @case('done')
                                            <span class="badge bg-success">Выполнена</span>
                                            @break
                                        @case('canceled')
                                            <span class="badge bg-danger">Отменена</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($request->master)
                                        {{ $request->master->name }}
                                    @else
                                        <span class="text-muted">Не назначен</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('dispatcher.requests.show', $request->id) }}" class="btn btn-sm btn-info">Просмотр</a>

                                        @if($request->status === 'new')
                                            <!-- Назначить мастера -->
                                            <form method="POST" action="{{ route('dispatcher.requests.assign', $request->id) }}" class="d-flex gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="master_id" class="form-select form-select-sm" style="width: auto;" required>
                                                    <option value="">Выбрать мастера</option>
                                                    @foreach($masters as $master)
                                                        <option value="{{ $master->id }}">{{ $master->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-success">Назначить</button>
                                            </form>
                                        @endif

                                        @if(in_array($request->status, ['new', 'assigned']))
                                            <!-- Отменить заявку -->
                                            <form method="POST" action="{{ route('dispatcher.requests.cancel', $request->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Отменить заявку?')">Отменить</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
