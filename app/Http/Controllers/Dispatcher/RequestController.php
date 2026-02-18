<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Request;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    /**
     * Показать список всех заявок
     */
    public function index(HttpRequest $request)
    {
        $query = Request::with(['master', 'events']);

        // Фильтр по статусу
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Фильтр по мастеру
        if ($request->has('master') && $request->master !== '') {
            $query->where('assigned_to', $request->master);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();
        $masters = User::where('role', 'master')->get();

        return view('dispatcher.requests.index', compact('requests', 'masters'));
    }

    /**
     * Показать детали заявки с историей изменений
     */
    public function show($id)
    {
        $request = Request::with(['master', 'events.user'])->findOrFail($id);
        $masters = User::where('role', 'master')->get();

        return view('dispatcher.requests.show', compact('request', 'masters'));
    }

    /**
     * Назначить мастера на заявку
     */
    public function assign(HttpRequest $request, $id)
    {
        $validated = $request->validate([
            'master_id' => 'required|exists:users,id',
        ]);

        $requestModel = Request::where('id', $id)
            ->where('status', 'new')
            ->first();

        if (!$requestModel) {
            return back()->with('error', 'Заявка не найдена или не может быть назначена');
        }

        $requestModel->assigned_to = $validated['master_id'];
        $requestModel->status = 'assigned';
        $requestModel->save();

        return back()->with('success', 'Мастер назначен на заявку');
    }

    /**
     * Отменить заявку
     */
    public function cancel($id)
    {
        $requestModel = Request::where('id', $id)
            ->whereIn('status', ['new', 'assigned'])
            ->first();

        if (!$requestModel) {
            return back()->with('error', 'Заявка не найдена или не может быть отменена');
        }

        $requestModel->status = 'canceled';
        $requestModel->save();

        return back()->with('success', 'Заявка отменена');
    }
}
