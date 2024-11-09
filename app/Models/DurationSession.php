<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DurationSession extends Model
{
    
    protected $fillable = [
        'date', 'start_time', 'end_time', 'slot_duration'
    ];

    
    public function slots()
    {
        return $this->hasMany(Slot::class, 'duration_session_id');  
    }

    
    public function labs()
    {
        return $this->hasManyThrough(Lab::class, Slot::class, 'duration_session_id', 'id', 'id', 'lab_id');
    }
}
