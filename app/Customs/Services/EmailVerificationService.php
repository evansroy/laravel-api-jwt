<?php

namespace App\Customs\Services;

use App\Models\EmailVerificationToken;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Models\User;

class EmailVerificationService
{
    /**
     * Send verification link to the user
     */
    public function sendVerificationLink(object $user)
    {
        Notification::send($user, new EmailVerificationNotification($this->generateVerificationLink($user->email)));
    }

    /**
     * Check if user has Already verified email
     */
    public function checkIfEmailIsVerified(object $user) {
        if ($user->email_verified_at) {
           response()->json([
               'status' => 'failed',
               'message' => 'Email already verified'
           ])->send();
           exit;
        }
    }

    /**
     * Verify user email
     */
    public function verifyEmail(string $token, string $email) {
        $user = User::where('email', $email)->first();
        if (!$user) {
            response()->json([
                'status' => 'failed',
                'message' => 'User not found'
            ])->send();
            exit;
        }
        $this->checkIfEmailIsVerified($user); 
       $verifiedToken = $this->verifyToken($token, $email);
       if($user->markEmailAsVerified()) {
           $verifiedToken->delete();
           return response()->json([
               'status' => 'success',
               'message' => 'Email verified successfully'
           ]);
       } else{
           return response()->json([
               'status' => 'failed',
               'message' => 'Email verification failed, Please try againlater'
           ]);
       }
       
    }
    /**
     * Verify Token
     */
    public function verifyToken(string $token, string $email)
    {
        $token = EmailVerificationToken::where('token', $token)->where('email', $email)->first();
        if ($token) {
           if($token->expires_at < now()) {
               return $token;
           } else {
               response()->json([
                   'status' => 'failed',
                   'message' => 'Token expired'
               ])->send();
               exit;
           }
        } else {
            response()->json([
                'status' => 'failed',
                'message' => 'Invalid token'
            ], 400)->send();
            exit;
        }
    }
    /**
     * 
     * Generate verification link
     */

    public function generateVerificationLink(string $email)
    {
        $checkTokenExists = EmailVerificationToken::where('email', $email)->first();
        if ($checkTokenExists) $checkTokenExists->delete();
        $token = Str::uuid();
        $url = config('app.url') . "?token=" . $token . "&email=" . $email;
        $saveToken = EmailVerificationToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
        ]);

        if ($saveToken) {
            return $url;
        }
    }
}
