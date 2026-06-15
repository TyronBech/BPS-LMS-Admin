<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bk_books';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'accession',
        'call_number',
        'title',
        'parallel_title',
        'authors',
        'description',
        'edition',
        'isbn',
        'place_of_publication',
        'publisher',
        'copyrights',
        'remarks',
        'category_id',
        'cover_image',
        'digital_copy_url',
        'barcode',
        'book_type',
        'availability_status',
        'condition_status',
    ];
    protected $casts = [
        'description' => 'array',
        'authors' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function ($book) {
            if ($book->category_id) {
                $category = Category::find($book->category_id);
                if ($category) {
                    $category->increment('newly_acquired');
                    $category->increment('present_inventory');
                }
            }
        });

        static::updated(function ($book) {
            // Handle Category Change (only for active books)
            $oldCategoryId = $book->getOriginal('category_id');
            $newCategoryId = $book->category_id;
            $isDeleted = $book->deleted_at !== null;

            if ($oldCategoryId !== $newCategoryId && !$isDeleted) {
                $oldCategory = Category::find($oldCategoryId);
                if ($oldCategory) {
                    $oldCategory->decrement('present_inventory');
                }
                $newCategory = Category::find($newCategoryId);
                if ($newCategory) {
                    $newCategory->increment('present_inventory');
                }
            }
        });

        static::deleted(function ($book) {
            if ($book->category_id) {
                $category = Category::find($book->category_id);
                if ($category) {
                    if ($book->isForceDeleting()) {
                        // Hard deleted.
                        // We only decrement present_inventory if it was not already soft-deleted.
                        $wasSoftDeleted = $book->getOriginal('deleted_at') !== null;
                        if (!$wasSoftDeleted) {
                            $category->decrement('present_inventory');
                        }
                    } else {
                        // Soft deleted.
                        $category->increment('discarded');
                        $category->decrement('present_inventory');
                    }
                }
            }
        });

        static::restored(function ($book) {
            if ($book->category_id) {
                $category = Category::find($book->category_id);
                if ($category) {
                    $category->decrement('discarded');
                    $category->increment('present_inventory');
                }
            }
        });
    }

    public static function getTableName()
    {
        return (new self())->getTable();
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'book_id', 'id');
    }
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'book_id', 'id');
    }

    public function subjectAccessCodes(): BelongsToMany
    {
        return $this->belongsToMany(SubjectAccessCode::class, 'bk_book_subject_access_code', 'book_id', 'subject_access_code_id')->withTimestamps();
    }

    /**
     * Get the active borrowing transaction for the book.
     */
    public function activeBorrow(): HasOne
    {
        return $this->hasOne(Transaction::class, 'book_id', 'id')
            ->where('transaction_type', 'Borrowed')
            ->whereIn('status', ['Borrowed', 'Overdue']);
    }

    /**
     * Get the active reservation transaction for the book.
     */
    public function activeReservation(): HasOne
    {
        return $this->hasOne(Transaction::class, 'book_id', 'id')
            ->where('transaction_type', 'Reserved')
            ->whereIn('status', ['Reserved', 'Pending', 'Available for pick up']);
    }

    /**
     * Scope a query to only include books that are currently borrowed and reserved.
     */
    public function scopeBorrowedAndReserved($query)
    {
        return $query->where('availability_status', 'Borrowed')
            ->whereHas('transactions', function ($q) {
                $q->where('transaction_type', 'Reserved')
                  ->whereIn('status', ['Reserved', 'Pending', 'Available for pick up']);
            });
    }

    /**
     * Check if the book is currently borrowed and has an active reservation.
     *
     * @return bool
     */
    public function getIsBorrowedAndReservedAttribute(): bool
    {
        return $this->availability_status === 'Borrowed' && $this->activeReservation()->exists();
    }
}
