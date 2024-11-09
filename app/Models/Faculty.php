<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    
    protected $table = 'faculties';

    
    protected $fillable = [
        'username', 
    ];

    
    protected $hidden = [];

    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function courses()
{
    return $this->hasMany(Course::class);
}

}
