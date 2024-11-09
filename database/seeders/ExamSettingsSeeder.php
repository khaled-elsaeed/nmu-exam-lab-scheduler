<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    { 
        DB::table('exam_settings')->insert([
            'start_date' => Carbon::create(2024, 11, 12)->toDateString(), 
            'end_date' => Carbon::create(2024, 11, 21)->toDateString(), 
            'daily_start_time' => '09:00:00', 
            'daily_end_time' => '16:00:00', 
            'time_slot_duration' => 45, 
            'rest_period' => 15, 
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("Exam settings seeded successfully.");
    }
}
