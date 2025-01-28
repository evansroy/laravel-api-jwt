<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Js;

class AuthController extends Controller
{
    /***
     * Login Method
     */

     public function login()
     {
         //
     }

     /**
      * Register Method
      */

      public function register(RegistrationRequest $request)
      {
          $user = User::create($request->validated());

          if ($user) {
             $token = auth()->login($user);
             return $this->responseWithToken($token, $user);
          } else {
                return response()->json([
                    'status' => 'failed',
                    'massage' => 'An error occurred while trying to create user',
                ], 500);
          }
      }

      /**
       * Return JWT Access Token
       */

       public function responseWithToken($token, $user)
       {
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'access_token' => $token,
                'type' => 'bearer',
            ]);
       }
}
