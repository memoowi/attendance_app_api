<?php

namespace App\Http\Controllers;

use DateTime;

class ServerTimeController extends Controller
{
    public function serverTime()
    {
        $getTime = new DateTime();
        if ($getTime === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get server time',
            ], 500);
        }
        return response()->json([
            'status' => 'success',
            'data' => $getTime,
        ]);
    }
}
