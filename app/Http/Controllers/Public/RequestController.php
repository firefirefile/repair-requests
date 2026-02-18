<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    /**
     * Показать форму создания заявки
     */
    public function create()
    {
        return view('public.requests.create');
    }

    /**
     * Сохранить новую заявку
     */
    public function store(HttpRequest $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'problem_text' => 'required|string',
        ]);

        // Создаем заявку через Eloquent для срабатывания observers
        $requestModel = new Request();
        $requestModel->client_name = $validated['client_name'];
        $requestModel->clientName = $validated['client_name']; // для совместимости
        $requestModel->phone = $validated['phone'];
        $requestModel->address = $validated['address'];
        $requestModel->problem_text = $validated['problem_text'];
        $requestModel->problemText = $validated['problem_text']; // для совместимости
        $requestModel->status = 'new';
        $requestModel->save();

        return redirect()->route('requests.create')
            ->with('success', 'Заявка успешно создана!');
    }
}
