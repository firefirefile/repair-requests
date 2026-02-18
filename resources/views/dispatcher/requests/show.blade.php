@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Детали заявки #{{ $request->id }}</h1>

    <div class="row">
        <div class="col-md-8">
            <!-- Информация о заявке -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Информация о заявке</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 150px;">Клиент</th>
                            <td>{{ $request->client_name }}</td>
                        </tr>
                        <tr>
                            <th>Телефон</th>
                            <td>{{ $request->phone }}</td>
                        </tr>
                        <tr>
                            <th>Адрес</th>
                            <td>{{ $request->address }}</td>
                        </tr>
                        <tr>
                            <th>Проблема</th>
                            <td>{{ $request->problem_text }}</td>
                        </tr>
                        <tr>
                            <th>Статус</th>
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
                        </tr>
                        <tr>
                            <th>Назначенный мастер</th>
                            <td>
                                @if($request->master)
                                    {{ $request->master->name }} ({{ $request->master->email }})
                                @else
                                    <span class="text-muted">Не назначен</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Создана</th>
                            <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Обновлена</th>
                            <td>{{ $request->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    </table>

                    <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                        <a href="{{ route('dispatcher.requests.index') }}" class="btn btn-secondary">Назад к списку</a>

                        @if($request->status === 'new')
                            <form method="POST" action="{{ route('dispatcher.requests.assign', $request->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="master_id" class="form-select d-inline-block" style="width: auto;" required>
                                    <option value="">Выбрать мастера</option>
                                    @foreach($masters as $master)
                                        <option value="{{ $master->id }}">{{ $master->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-success">Назначить</button>
                            </form>
                        @endif

                        @if(in_array($request->status, ['new', 'assigned']))
                            <form method="POST" action="{{ route('dispatcher.requests.cancel', $request->id) }}" class="d-inline" onsubmit="return confirm('Отменить заявку?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger">Отменить заявку</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- История изменений -->
            <div class="card history-card">
                <div class="card-header">
                    <h5 class="mb-0">История изменений</h5>
                </div>
                <div class="card-body">
                    @if($request->events->isEmpty())
                        <p class="text-muted">Нет истории изменений</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($request->events->sortByDesc('created_at') as $event)
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted">{{ $event->created_at->format('d.m.Y H:i') }}</small>
                                        <small class="text-muted">{{ $event->user ? $event->user->name : 'Система' }}</small>
                                    </div>
                                    <div class="mt-1">
                                        @switch($event->action)
                                            @case('created')
                                                <span class="badge bg-primary">Создана</span>
                                                <span class="ms-2">Статус: {{ $event->new_status }}</span>
                                                @break
                                            @case('status_change')
                                                <span class="badge bg-info">Изменен статус</span>
                                                <span class="ms-2">
                                                    @if($event->old_status)
                                                        {{ $event->old_status }} → {{ $event->new_status }}
                                                    @else
                                                        {{ $event->new_status }}
                                                    @endif
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $event->action }}</span>
                                        @endswitch
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
