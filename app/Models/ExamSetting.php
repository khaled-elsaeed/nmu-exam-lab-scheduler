<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExamSetting extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'daily_start_time',
        'daily_end_time',
        'time_slot_duration',
        'rest_period',
    ];

   

    
    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }
}
