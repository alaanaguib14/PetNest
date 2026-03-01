<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuthController extends Controller
{
    public function registerAdmin(RegisterRequest $request){
        $validated = $request->validated();
        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => 1,
        ]);

        event(new Registered($admin));
        $token = JWTAuth::fromUser($admin);
        return response()->json([
            'success' => true,
            'message' => 'Admin registered successfully. Please verify your email.',
            'access_token' => $token,
            'verified' => false,
        ], 201);
    }
}
