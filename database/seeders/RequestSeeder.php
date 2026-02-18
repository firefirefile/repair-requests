<?php

namespace Database\Seeders;

use App\Models\Request;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем 10 новых заявок
        Request::factory()->count(10)->newRequest()->create();

        // Создаем 5 назначенных заявок
        Request::factory()->count(5)->assignedRequest()->create();

        // Создаем 3 в работе
        Request::factory()->count(3)->inProgressRequest()->create();

        // Создаем 5 завершенных
        Request::factory()->count(5)->doneRequest()->create();

        // Создаем 2 отмененных
        Request::factory()->count(2)->canceledRequest()->create();
    }
}
