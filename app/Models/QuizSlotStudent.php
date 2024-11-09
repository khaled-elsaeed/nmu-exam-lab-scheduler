<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSlotStudent extends Model
{
    protected $table = 'quiz_slot_student'; 

    protected $fillable = ['quiz_id','slot_id','student_id'];

    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id'); 
    }

    
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id'); 
    }

    
    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slot_id'); 
    }
}
