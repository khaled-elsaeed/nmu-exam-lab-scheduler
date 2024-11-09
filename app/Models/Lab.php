<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    
    protected $table = 'labs';

    
    protected $fillable = [
        'building', 
        'floor', 
        'number', 
        'capacity'
    ];

    
    
    public function slots()
    {
        return $this->hasMany(Slot::class);  
    }
}
