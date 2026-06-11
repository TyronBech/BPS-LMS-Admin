<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryClassReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'library_class_reservations';

    protected $fillable = [
        'user_id',
        'faculty_user_id',
        'reservation_date',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'approved_by',
        'approved_at',
        'rejected_at',
        'remarks',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the user who requested the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the faculty sponsoring/involved in the reservation.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_user_id');
    }

    /**
     * Get the admin user who approved the reservation.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
