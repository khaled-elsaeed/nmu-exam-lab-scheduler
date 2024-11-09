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

   
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isResident(): bool
    {
        return $this->hasRole('resident');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }


    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }

    

    public static function findUserByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }


    
}
