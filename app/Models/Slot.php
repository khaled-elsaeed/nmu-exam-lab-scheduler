<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $table = 'slots';
    
    protected $fillable = [
        'lab_id', 'duration_session_id', 'slot_number', 'start_time', 'end_time','max_students',
        'current_students', 
    ];

    
    public function session()
    {
        return $this->belongsTo(DurationSession::class, 'duration_session_id');
    }

    
    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

     
     public function quizzes()
     {
         return $this->belongsToMany(Quiz::class, 'quiz_slot_student')
                     ->withTimestamps();
     }
 
     
     public function students()
     {
         return $this->belongsToMany(Student::class, 'quiz_slot_student')
                     ->withTimestamps();
     }


}


