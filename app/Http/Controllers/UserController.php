<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'data' => $user,
                'token' => $token,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'validation error',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    } 

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            $credentials = $request->only(['email', 'password']);
            $user = User::where('email', $credentials['email'])->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 401);
            } 
            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password incorrect',
                ], 401);
            }
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'data' => $user,
                'token' => $token,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'validation error',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete(); // delete current token from database
        return response()->json([
            'status' => 'success',
            'message' => 'Logout successfully'
        ], 200);
    }
}
