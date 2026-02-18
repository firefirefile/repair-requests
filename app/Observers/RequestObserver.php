<?php

namespace App\Observers;

use App\Models\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class RequestObserver
{
    /**
     * Handle the Request "updating" event.
     */
    public function updating(Request $request): void
    {
        // Only log if the status is changing
        if ($request->isDirty('status')) {
            $oldStatus = $request->getOriginal('status');
            $newStatus = $request->status;

            Event::create([
                'user_id' => Auth::id(),
                'request_id' => $request->id,
                'action' => 'status_change',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }
    }

    /**
     * Handle the Request "created" event.
     */
    public function created(Request $request): void
    {
        Event::create([
            'user_id' => Auth::id(), // Can be null if no authenticated user
            'request_id' => $request->id,
            'action' => 'created',
            'old_status' => null,
            'new_status' => $request->status,
        ]);
    }
}
