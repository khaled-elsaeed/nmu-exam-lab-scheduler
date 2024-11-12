<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'course_id'];



public function course()
{
    return $this->belongsTo(Course::class);
}


public function slots()
{
    return $this->belongsToMany(Slot::class, 'quiz_slot_student')
                ->withTimestamps();
}



public function students()
{
    return $this->belongsToMany(Student::class, 'quiz_student');  
}




public function slotStudents()
{
    return $this->belongsToMany(Student::class, 'quiz_slot_student')
                ->withPivot('slot_id')  
                ->withTimestamps();  
}

}

