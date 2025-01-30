<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Customs\Services\EmailVerificationService;
use App\Http\Requests\ResendEmailVerificationLinkRequest;


class AuthController extends Controller
{
    public function __construct(private EmailVerificationService $service) {}

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="Login a user",
     *     description="Logs in a user and returns a JWT token if the credentials are valid.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Email not verified")
     * )
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth()->user();

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Your email is not verified. Please verify your email before logging in.',
            ], 403);
        }

        return $this->responseWithToken($token, $user);
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Creates a new user account and sends an email verification link.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", nullable=true, example=null)
     *             ),
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1N..."),
     *             @OA\Property(property="type", type="string", example="bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The email field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while creating the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="An error occurred while trying to create user")
     *         )
     *     )
     * )
     */
    public function register(RegistrationRequest $request)
    {
        $user = User::create($request->validated());

        if ($user) {
            $this->service->sendVerificationLink($user);
            $token = auth()->login($user);

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'access_token' => $token,
                'type' => 'bearer',
            ], 201);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while trying to create user',
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/auth/resend-email-verification",
     *     tags={"Authentication"},
     *     summary="Resend email verification link",
     *     description="Resends the email verification link to the user's registered email.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Verification email resent"),
     *     @OA\Response(response=400, description="Invalid email address")
     * )
     */
    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {
        return $this->service->resendVerificationLink($request->email);
    }

    /**
     * @OA\Post(
     *     path="/auth/verify-email",
     *     tags={"Authentication"},
     *     summary="Verify user email",
     *     description="Verifies the user's email using the provided token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "token"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="sample_verification_token")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Email verified successfully"),
     *     @OA\Response(response=400, description="Invalid verification token")
     * )
     */
    public function verifyUserEmail(VerifyEmailRequest $request)
    {
        return $this->service->verifyEmail($request->token, $request->email);
    }

    /**
     * @OA\Schema(
     *     schema="TokenResponse",
     *     @OA\Property(property="status", type="string", example="success"),
     *     @OA\Property(property="user", type="object"),
     *     @OA\Property(property="access_token", type="string", example="your_jwt_token"),
     *     @OA\Property(property="type", type="string", example="bearer")
     * )
     *
     * @OA\Post(
     *     path="/auth/token",
     *     tags={"Authentication"},
     *     summary="Return JWT access token",
     *     description="Returns a JWT access token for authenticated users.",
     *     @OA\Response(response=200, description="Token response", @OA\JsonContent(ref="#/components/schemas/TokenResponse"))
     * )
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
