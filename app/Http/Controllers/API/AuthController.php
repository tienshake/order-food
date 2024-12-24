<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }


    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:users',
            'password' => 'required|min:6',
            'email' => 'required|email|unique:users',
        ]);

        /** @var \App\Models\User $user **/
        $user = User::create([
            ...$validated,
            'password' => Hash::make($request->password),
            'role' => 'customer'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => ['token' => $token, 'user' => $user],
            'message' => 'Register successful'
        ]);
    }
}
