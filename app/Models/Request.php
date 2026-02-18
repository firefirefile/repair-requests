<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'client_name',
        'clientName',
        'phone',
        'address',
        'problem_text',
        'problemText',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the events for the request.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'request_id');
    }

    /**
     * Get the status history for the request.
     */
    public function statusHistory(): HasMany
    {
        return $this->events()->whereNotNull('old_status')->orWhereNotNull('new_status');
    }
}
