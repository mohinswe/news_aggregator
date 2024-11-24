<?php
namespace App\Http\Controllers;


use App\Models\User;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\forgotPasswordRequest;

class AuthController extends Controller
{
    public function __construct(public AuthService $authService)
    {
        
    }
    
    // register a new user
    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request);
    }

    // User login
    public function login(LoginRequest $request)
    {
        return $this->authService->login($request);
    }

    // User logout
    public function logout()
    {
        return $this->authService->logout();
    }

    // Forgot password
    public function forgotPassword(forgotPasswordRequest $request)
    {
        return $this->authService->forgotPassword($request);
    }

    // Reset password
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Verify OTP
        $cachedOtp = Cache::get('otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // Reset the password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Clear the OTP from the cache
        Cache::forget('otp_' . $request->email);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
    
}