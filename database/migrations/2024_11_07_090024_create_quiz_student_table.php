<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_student', function (Blueprint $table) {
            $table->id();                                    
            $table->foreignId('student_id')->constrained()->onUpdate('cascade')->onDelete('cascade'); 
            $table->foreignId('quiz_id')->constrained()->onUpdate('cascade')->onDelete('cascade');    
            $table->unique(['student_id', 'quiz_id']);
            $table->timestamps();                             
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_student');
    }
};
