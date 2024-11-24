<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;

class AuthService {

    public function register($request) {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user'    => $user
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function login($request)
    {
        try {
            $user = User::where('email', $request->email)->first();
    
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully',
                'token'   => $user->createToken('auth_token')->plainTextToken,
            ], 200);
    
        } catch (\Throwable $th) {
            // Log the exception for debugging
            Log::error('Login Error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
    
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }
    

    public function logout() {
        try {

            Auth::user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function forgotPassword($request) {
        try{ 
            // Check if the user exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Generate a 6-digit OTP
            $otp = rand(100000, 999999);

            // Store OTP in the cache with a 10-minute expiration
            Cache::put('otp_' . $user->email, $otp, now()->addMinutes(10));

            // Send OTP via email (you can use SMS instead)
            Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Your Password Reset OTP');
            });

            // Return a success message
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email'
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function resetPassword($request) {
        try {


            // Verify OTP
            $cachedOtp = Cache::get('otp_' . $request->email);

            if (!$cachedOtp || $cachedOtp != $request->otp) {
                return response()->json(['message' => 'Invalid or expired OTP'], 400);
            }

            // Reset the password
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            // Clear the OTP from the cache
            Cache::forget('otp_' . $request->email);

            return response()->json(['message' => 'Password reset successfully']);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}