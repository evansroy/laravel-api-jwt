<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Js;
use App\Customs\Services\EmailVerificationService;

class AuthController extends Controller
{
    public function __construct(private EmailVerificationService $service)
    {
        
    }
    /***
     * Login Method
     */

     public function login(LoginRequest $request)
     {
         $token = auth()->attempt($request->validated());

         if ($token) {
             return $this->responseWithToken($token, auth()->user());
         } else {
             return response()->json([
                 'status' => 'failed',
                 'massage' => 'Invalid credentials',
             ], 401);
         }
     }

     /**
      * Register Method
      */

      public function register(RegistrationRequest $request)
      {
          $user = User::create($request->validated());

          if ($user) {
            $this->service->sendVerificationLink($user);
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
