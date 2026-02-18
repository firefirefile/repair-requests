<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Показать список заявок, назначенных на текущего мастера
     */
    public function index()
    {
        $requests = Request::with('master')
            ->where('assigned_to', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('master.requests.index', compact('requests'));
    }

    /**
     * Показать детали заявки с историей изменений
     */
    public function show($id)
    {
        $request = Request::with(['master', 'events.user'])
            ->where('assigned_to', Auth::id())
            ->findOrFail($id);

        return view('master.requests.show', compact('request'));
    }

    /**
     * Взять заявку в работу (с защитой от гонок)
     */
    public function take(HttpRequest $request, $id)
    {
        $requestModel = Request::where('id', $id)
            ->where('assigned_to', Auth::id())
            ->where('status', 'assigned')
            ->first();

        if (!$requestModel) {
            return back()->with('error', 'Заявка не найдена или уже взята');
        }

        // ОПТИМИСТИЧНАЯ БЛОКИРОВКА: проверяем, что updated_at не изменился
        $updated = Request::where('id', $id)
            ->where('assigned_to', Auth::id())
            ->where('status', 'assigned')
            ->where('updated_at', $requestModel->updated_at)
            ->update([
                'status' => 'in_progress',
                'updated_at' => now()
            ]);

        if (!$updated) {
            return back()->with('error', 'Заявка была изменена другим запросом. Пожалуйста, обновите страницу.');
        }

        return back()->with('success', 'Заявка взята в работу');
    }

    /**
     * Завершить заявку
     */
    public function complete($id)
    {
        $requestModel = Request::where('id', $id)
            ->where('assigned_to', Auth::id())
            ->where('status', 'in_progress')
            ->first();

        if (!$requestModel) {
            return back()->with('error', 'Заявка не найдена или не может быть завершена');
        }

        $requestModel->status = 'done';
        $requestModel->save();

        return back()->with('success', 'Заявка завершена');
    }
}
