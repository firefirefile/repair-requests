@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Мои заявки</h1>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Список назначенных заявок</h5>
        </div>
        <div class="card-body">
            @if($requests->isEmpty())
                <p class="text-center text-muted">Нет назначенных заявок</p>
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
                                <td>{{ Str::limit($request->problem_text, 50) }}</td>
                                <td>
                                    @switch($request->status)
                                        @case('assigned')
                                            <span class="badge bg-warning">Назначена</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-info">В работе</span>
                                            @break
                                        @case('done')
                                            <span class="badge bg-success">Выполнена</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('master.requests.show', $request->id) }}" class="btn btn-sm btn-info">Просмотр</a>

                                        @if($request->status === 'assigned')
                                            <form method="POST" action="{{ route('master.requests.take', $request->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-primary">Взять в работу</button>
                                            </form>
                                        @endif

                                        @if($request->status === 'in_progress')
                                            <form method="POST" action="{{ route('master.requests.complete', $request->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Завершить</button>
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
