<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $attendances = $user->attendances->toArray();

        // Convert created_at and updated_at timestamps to Asia/Jakarta timezone
        foreach ($attendances as &$attendance) {
            $attendance['created_at'] = Carbon::parse($attendance['created_at'])->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
            $attendance['updated_at'] = Carbon::parse($attendance['updated_at'])->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        }

        return response()->json([
            'status' => 'success',
            'data' => $attendances
        ]);
    }
    public function clock(Request $request)
    {
        $user = auth()->user();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('clock_in', today())->first();
        try {
            $request->validate([
                'latitude' => 'required',
                'longitude' => 'required'
            ]);
            if ($attendance) {
                if ($attendance->clock_out) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You have already clocked out today',
                    ], 400);
                }
                $attendance->update([
                    'clock_out' => now(),
                    'latitude_out' => $request->latitude,
                    'longitude_out' => $request->longitude
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Clock out successfully',
                ], 200);
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'clock_in' => now(),
                    'latitude_in' => $request->latitude,
                    'longitude_in' => $request->longitude,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Clock in successfully',
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
}
