<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'email',
        'username',
        'password',
        'is_active',
        'profile_picture',
        'last_login',
        'faculty_id', // Add faculty_id to fillable
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'profile_picture' => 'string',
    ];

    // Relationship with Faculty model
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isFacultyAdmin(): bool
    {
        return $this->hasRole('faculty');
    }



    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }

    public static function findUserByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    // Helper method to get faculty ID for a user
    public function getFacultyId(): ?int
    {
        return $this->faculty_id;
    }
}

