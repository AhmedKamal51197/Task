<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\VerificationMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Register and send verification code to mail 
     * Log verification code
     */
    public function Register(RegisterRequest $request)
    {
        $userData = $request->validated();
        $verificationCode = rand(100000, 999999);
        $user = User::create([
            'name' => $userData['name'],
            'phone_number' => $userData['phone_number'],
            'email' => $userData['email'],
            'password' => $userData['password'],
            'verification_code' => $verificationCode,
        ]);
        Log::info("Verification code for user {$user->email}: {$verificationCode}");
        $accessToken = $user->createToken('auth_token')->plainTextToken;
        Mail::to($user->email)->send(new VerificationMail($verificationCode));
        return response()->json([
            'status' => 'success',
            'verification_code' => 'send verification code to your email',
            'user' => $user,
            'access_token' => $accessToken
        ], 200);
    }
    public function login(LoginRequest $request)
    {
        $userData = $request->validated();
        $logedIn = Auth::attempt($userData);
        $user = Auth::user();
        //dd($user->email_verified_at);
       // dd($logedIn);
        if (!$logedIn) {

            return response()->json([
                'status' => 'failed',
                'message' => 'incorrect email or password'
            ], 404);

        } else if (!$user->is_verify) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email Not verified'
            ], 403);
        }

        $accessToken = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'access_token' => $accessToken
        ], 404);
    }
    public function verifyUser(Request $request)
    {
        $validatedData = $request->validate([
            'email' => ['required','email'],
            'verification_code' => ['required', 'string', 'min:6']
        ]);
        $user = User::where('email', $validatedData['email'])->first();
        if (!$user ) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found'
            ], 404);
        }else if( $user->verification_code !== $validatedData['verification_code'])
        {
            return response()->json([
                'status' => 'failed',
                'message' => 'Verifcation code invalid'
            ], 404);
        }

        $user->markEmailAsVerified();
        $user->update([
            'is_verify'=>1,
            'verification_code'=>null
        ]);
        

        return response()->json([
            'status' => 'success',
            'message' => 'email verified successfully'
        ], 200);
    }
    public function resendVerificationCode(Request $request)
    {
        $validatedData=$request->validate(['email'=>['required','email:rfc,dns']]);
        $email=$validatedData['email'];
        $user=User::where('email',$email)->first();
        if(!$user){
            return response()->json([
                'status'=>'failed',
                'message'=>'user not found'
            ],404);
        }
        $verificationCode=rand(100000,999999);
        $user->update(
            [
                'verification_code'=>$verificationCode
            ]
            );
        Mail::to($email)->send(new VerificationMail($verificationCode));
        return response()->json([
            'status'=>'success',
            'message'=>'vreification code send to your email successfully'
        ],200);
    }
    
}
