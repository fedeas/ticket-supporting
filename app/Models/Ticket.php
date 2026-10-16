<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'registration_number',
        'customer_id',
        'department',
        'station_unit',
        'contact_person',
        'contact_phone',
        'installation_location',
        'vehicle_registration',
        'report_channel',
        'equipment_types',
        'has_request',
        'request_text',
        'title',
        'description',
        'fault_description',
        'status',
        'opened_at',
        'closed_at',
        'recorded_by',
        'internal_notes',
        'resolved_at',
        'resolution_action',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'equipment_types' => 'array',
            'has_request' => 'boolean',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function updates()
    {
        return $this->hasMany(TicketUpdate::class)->orderByDesc('action_at');
    }

    public function getOpenedByNameAttribute(): string
    {
        if (! empty($this->recorded_by)) {
            return $this->recorded_by;
        }

        if ($this->relationLoaded('customer') && $this->customer) {
            return $this->customer->username
                ?? $this->customer->name
                ?? $this->customer->email
                ?? 'Άγνωστο';
        }

        return 'Άγνωστο';
    }

    public function getVehicleNumberAttribute(): string
    {
        return $this->vehicle_registration ?: '—';
    }
}
