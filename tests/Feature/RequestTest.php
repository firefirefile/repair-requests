<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Request;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Тест 1: Создание заявки через публичную форму
     */
    public function test_public_can_create_request(): void
    {
        $data = [
            'client_name' => 'Тестовый Клиент',
            'phone' => '+79991234567',
            'address' => 'ул. Тестовая, д. 1',
            'problem_text' => 'Сломался тестовый телевизор',
        ];

        $response = $this->post(route('requests.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('requests', [
            'client_name' => 'Тестовый Клиент',
            'phone' => '+79991234567',
        ]);
    }

    /**
     * Тест 2: Защита от гонок при взятии заявки в работу
     */
    public function test_race_condition_prevention(): void
    {
        $master = User::factory()->create(['role' => 'master']);

        $request = Request::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $master->id,
        ]);

        $this->actingAs($master);

        $firstResponse = $this->patch(route('master.requests.take', $request->id));
        $firstResponse->assertSessionHas('success');

        $request->refresh();
        $this->assertEquals('in_progress', $request->status);

        $secondResponse = $this->patch(route('master.requests.take', $request->id));
        $secondResponse->assertSessionHas('error');
    }

    /**
     * Тест 3: Аудит-лог при создании заявки
     */
    public function test_audit_log_on_request_creation(): void
    {
        $dispatcher = User::factory()->create(['role' => 'dispatcher']);

        $this->actingAs($dispatcher);

        $data = [
            'client_name' => 'Аудит Клиент',
            'phone' => '+79991234568',
            'address' => 'ул. Аудитная, д. 2',
            'problem_text' => 'Тест аудита',
        ];

        $response = $this->post(route('requests.store'), $data);
        $response->assertRedirect();

        // Get the created request
        $request = Request::where('client_name', 'Аудит Клиент')->first();

        $this->assertNotNull($request);

        // Check that an event was created
        $event = Event::where('request_id', $request->id)->first();
        $this->assertNotNull($event);
        $this->assertEquals('created', $event->action);
        $this->assertEquals('new', $event->new_status);
        $this->assertNull($event->old_status);
        $this->assertEquals($dispatcher->id, $event->user_id);
    }

    /**
     * Тест 4: Аудит-лог при изменении статуса
     */
    public function test_audit_log_on_status_change(): void
    {
        $dispatcher = User::factory()->create(['role' => 'dispatcher']);
        $master = User::factory()->create(['role' => 'master']);

        $this->actingAs($dispatcher);

        // Create a request
        $request = Request::factory()->create([
            'status' => 'new',
            'assigned_to' => null,
        ]);

        // Assign a master (changes status from new to assigned)
        $response = $this->patch(route('dispatcher.requests.assign', $request->id), [
            'master_id' => $master->id,
        ]);
        $response->assertRedirect();

        $request->refresh();

        // Check that an event was created for status change
        $event = Event::where('request_id', $request->id)
            ->where('action', 'status_change')
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals('new', $event->old_status);
        $this->assertEquals('assigned', $event->new_status);
        $this->assertEquals($dispatcher->id, $event->user_id);
    }

    /**
     * Тест 5: История статусов доступна через отношение
     */
    public function test_status_history_relationship(): void
    {
        $dispatcher = User::factory()->create(['role' => 'dispatcher']);
        $master = User::factory()->create(['role' => 'master']);

        $this->actingAs($dispatcher);

        // Create a request
        $request = Request::factory()->create(['status' => 'new']);

        // Change status multiple times
        $request->update(['status' => 'assigned', 'assigned_to' => $master->id]);
        $request->refresh();

        $request->update(['status' => 'in_progress']);
        $request->refresh();

        $request->update(['status' => 'done']);
        $request->refresh();

        // Check that we have the correct number of status change events
        $statusEvents = $request->events()->where('action', 'status_change')->count();
        $this->assertEquals(3, $statusEvents); // new->assigned, assigned->in_progress, in_progress->done

        // Check that creation event exists
        $createdEvent = $request->events()->where('action', 'created')->first();
        $this->assertNotNull($createdEvent);
    }
}
