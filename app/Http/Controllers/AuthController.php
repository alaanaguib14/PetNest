<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Password;
class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $validated = $request->validated();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => 2,
        ]);    

        event(new Registered($user));
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Registered successfully. Please verify your email.',
            'access_token' => $token,
            'verified' => false,
        ], 201);
    }
    public function login(Request $request){
        $credentials = $request->validate([
            'email'=> 'required|email',
            'password'=> 'required',
        ]);

        $token = JWTAuth::attempt($credentials);
        if(!$token){
            return response()->json([
                'success'=>false,
                'message'=>'invalid Credentials',
            ], 401);
        };
        return response()->json([
            'success'=> true,
            'access_token'=> $token,
            'expires_in'=> JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    public function logout(){
        auth()->logout();
        return response()->json([
            'success' => true,
            'message' => 'Logged out'
        ], 200);
    }

    public function refresh(){
        $token = auth()->refresh();
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function forgotPassword(Request $request){
        $request->validate(['email' => 'required|email']);
        
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['success' => true, 'message' => __($status)], 200)
                    : response()->json(['success' => false, 'message' => __($status)], 500);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            [
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function ($user, $password){
                $user->password = $password;
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? response()->json(['success' => true, 'message' => __($status)], 200)
                    : response()->json(['success' => false, 'message' => __($status)], 500);
    }

    public function emailVerification(Request $request, $id,$hash){
        if (! $request->hasValidSignature()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid or expired link'
            ], 403);
        }
        $user = User::findOrFail($id);

        if(!hash_equals($hash,sha1($user->getEmailForVerification()))){
            return response()->json([
                'success' =>false,
                'message'=> 'invalid verification link',
            ], 403);
        }
        if ($user->hasVerifiedEmail()){
            return response()->json([
                'success' => true,
                'message' => 'email already verified',
            ]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'success' => true,
            'message' => 'email verified'
        ], 200);
    }
}
