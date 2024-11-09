<?php

namespace App\Http\Controllers;

use App\Models\ExamSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExamSettingController extends Controller
{
    public function show()
    {
        try {
            $examSettings = ExamSetting::first();

            // If no exam settings found
            if (!$examSettings) {
                return response()->json(['message' => 'No exam settings found.'], 404);
            }

            return view('admin.exam-settings', compact('examSettings'));
        } catch (\Exception $e) {
            Log::error('Error retrieving exam settings: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json(['message' => 'Failed to retrieve exam settings. Please try again later.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'daily_start_time' => 'required|date_format:H:i',
                'daily_end_time' => 'required|date_format:H:i',
                'time_slot_duration' => 'required|integer|min:1',
                'rest_period' => 'required|integer|min:1',
            ]);

            $examSetting = ExamSetting::findOrFail($id);

            $examSetting->update([
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'daily_start_time' => $validated['daily_start_time'],
                'daily_end_time' => $validated['daily_end_time'],
                'time_slot_duration' => $validated['time_slot_duration'],
                'rest_period' => $validated['rest_period'],
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Exam settings updated successfully.',
                'examSettings' => $examSetting
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating exam settings: ' . $e->getMessage(), [
                'exception' => $e,
                'exam_id' => $id,
                'input_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update exam settings. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
