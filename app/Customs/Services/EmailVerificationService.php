<?php

namespace App\Customs\Services;

use App\Models\EmailVerificationToken;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
class EmailVerificationService
{
    /**
     * Send verification link to the user
     */
    public function sendVerificationLink(object $user){
        Notification::send($user, new EmailVerificationNotification($this->generateVerificationLink($user->email)));
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
