<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'avatar',
        'gender',
        'phone_number',
        'password',
        'user_type',
        'is_active',
        'locale',
        'created_by',
        'updated_by',
    ];

    protected array $dontLog = ['password', 'remember_token'];

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function updatedUsers()
    {
        return $this->hasMany(User::class, 'updated_by');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function getNameAttribute()
    {
        $parts = array_filter([
            $this->first_name ?? null,
            $this->last_name ?? null,
        ], fn($p) => !is_null($p) && $p !== '');

        return $parts ? implode(' ', $parts) : null;
    }

    /**
     * Get all reviews written by this user as a client.
     */
    public function clientReviews()
    {
        return $this->hasMany(Review::class, 'client_id');
    }
}
