<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guarded = 'admin';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rfid',
        'group_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'profile_image',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function students() : HasOne
    {
        return $this->hasOne(StudentDetail::class, 'user_id', 'id');
    }
    public function employees() : HasOne
    {
        return $this->hasOne(EmployeeDetail::class, 'user_id', 'id');
    }
    public function visitors() : HasOne
    {
        return $this->hasOne(VisitorDetail::class, 'user_id', 'id');
    }
    public function groups() : BelongsTo
    {
        return $this->belongsTo(UserGroup::class, 'group_id', 'id');
    }
    public function logs() : HasMany
    {
        return $this->hasMany(Log::class, 'user_id', 'id');
    }
}
