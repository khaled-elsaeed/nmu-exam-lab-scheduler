<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'faculty_id'];

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function faculty() {
        return $this->belongsTo(Faculty::class);
    }
    
}
