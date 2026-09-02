<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Insert or update SMTP Settings
     */
    public function updateSmtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_mailer' => 'required|string', // e.g. smtp
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|string', // e.g. tls, ssl
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $settings = $request->only([
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 
            'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'
        ]);

        // Process and insert/update settings safely preserving created_by
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->update([
                    'value' => $value,
                    'updated_by' => $userId,
                ]);
            } else {
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'SMTP Settings updated successfully.'
        ], 200);
    }

    /**
     * Insert or update Twilio Settings
     */
    public function updateTwilio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'twilio_sid' => 'required|string',
            'twilio_auth_token' => 'required|string',
            'twilio_phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $settings = $request->only([
            'twilio_sid', 'twilio_auth_token', 'twilio_phone_number'
        ]);

        // Process and insert/update settings safely preserving created_by
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->update([
                    'value' => $value,
                    'updated_by' => $userId,
                ]);
            } else {
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Twilio Settings updated successfully.'
        ], 200);
    }
}
