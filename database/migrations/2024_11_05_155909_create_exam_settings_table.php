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
        Schema::create('exam_settings', function (Blueprint $table) {
            $table->id();                             
            $table->date('start_date');               
            $table->date('end_date');                 
            $table->time('daily_start_time');         
            $table->time('daily_end_time');           
            $table->integer('time_slot_duration')->default(45); 
            $table->integer('rest_period')->default(10); 
            $table->timestamps();                     
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_settings');
    }
};
