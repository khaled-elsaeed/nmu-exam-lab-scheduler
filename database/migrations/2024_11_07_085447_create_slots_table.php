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
        Schema::create('slots', function (Blueprint $table) {
            $table->id();                              
            $table->foreignId('duration_session_id')->constrained()->onDelete('cascade')->onUpdate('cascade'); 
            $table->integer('slot_number');            
            $table->time('start_time');                
            $table->time('end_time');   
            $table->foreignId('lab_id')->constrained('labs')->onUpdate('cascade')->onDelete('restrict');  
            $table->integer('max_students');  
            $table->integer('current_students')->default(0); 
            $table->timestamps();                      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
