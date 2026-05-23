<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportProgress extends Model
{
    protected $table = 'import_progress';

    protected $fillable = [
        'type',
        'status',
        'initiated_by',
        'total_rows',
        'processed_rows',
        'new_count',
        'updated_count',
        'error_message',
    ];

    protected $casts = [
        'total_rows'     => 'integer',
        'processed_rows' => 'integer',
        'new_count'      => 'integer',
        'updated_count'  => 'integer',
    ];

    /**
     * @return BelongsTo<User, ImportProgress>
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Returns true if this import is still in an active (non-terminal) state.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    /**
     * Returns the percentage completion as an integer 0–100.
     */
    public function progressPercent(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return (int) min(100, round(($this->processed_rows / $this->total_rows) * 100));
    }
}
