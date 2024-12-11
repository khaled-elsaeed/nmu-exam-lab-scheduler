<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'academic_id', 'national_id'];

    
    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_student', 'student_id', 'quiz_id');
    }

    public function slotQuizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_slot_student')
                    ->withPivot('slot_id')  
                    ->withTimestamps();  
    }
}

