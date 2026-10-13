<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;



class UserController extends Controller
{
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'massage' => 'Unauthorize'
            ], Response::HTTP_UNAUTHORIZED);
        };

         $user = Auth::user();

         $token = $user->createToken('auth_token')->plainTextToken;

         return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function register(Request $request)
    {
        $user = $request->validate([
            'name' => 'string | required',
            'email' => 'email | string | required | unique:users',
            'password' => 'string | required | min:8'
        ]);

        $user = User::create($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'user berhasil didaftarkan',
            'token' => $token,
            'type' => 'Bearer'
        ], Response::HTTP_OK);


    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'logout success'
        ]);
    }
}
