<?php

namespace App\Customs\Services;

use Illuminate\Support\Facades\Hash;

class PasswordService
{
    private function validateCurrentPassword($current_password) 
    {
        if(!password_verify($current_password, auth()->user()->password)){
            response()->json([
                'status' => 'failed',
                'message' => 'Password does not match the current password',
            ])->send();
            exit;
        }
    }
    public function changePassword($data)
    {
        $this->validateCurrentPassword($data['current_password']);
        # password current_password
        $updatePassword = auth()->user()->update([
            'password' => Hash::make($data['password'])
        ]);

        if ($updatePassword) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully',
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating password',
            ]);
        }
    }
}
