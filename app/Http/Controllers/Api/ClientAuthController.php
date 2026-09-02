<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClientAuthController extends Controller
{
    /**
     * Register client or trigger OTP for existing client
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // Mobile number or Email
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $identifier = $request->identifier;
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        // Find or Create User
        $user = User::firstOrCreate(
            [$isEmail ? 'email' : 'mobile_number' => $identifier],
            [
                'name' => 'Client',
                'password' => bcrypt(str()->random(16)), // Dummy password since they use OTP
                'is_verified' => false,
            ]
        );

        // Assign 'Client' role if not already assigned
        if (!$user->hasRole('Client')) {
            $user->assignRole('Client');
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        
        // Save to Cache for 10 minutes
        Cache::put('otp_' . $identifier, $otp, now()->addMinutes(10));

        // Simulate sending OTP (Log it instead of sending SMS/Email for now)
        Log::info("OTP for {$identifier} is: {$otp}");

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully',
        ], 200);
    }

    /**
     * Verify OTP and Login
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'otp' => 'required|numeric',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $identifier = $request->identifier;
        
        $cachedOtp = Cache::get('otp_' . $identifier);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired OTP'], 401);
        }

        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $user = User::where($isEmail ? 'email' : 'mobile_number', $identifier)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        // Mark verified
        if (!$user->is_verified) {
            $user->update(['is_verified' => true]);
        }

        // Update FCM if passed
        if ($request->has('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Clear OTP
        Cache::forget('otp_' . $identifier);

        // Generate Token
        $token = $user->createToken('client_auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Verified and logged in successfully',
            'data' => [
                'token' => $token,
                'user' => $user->load('roles')
            ]
        ], 200);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        return $this->register($request);
    }
}
