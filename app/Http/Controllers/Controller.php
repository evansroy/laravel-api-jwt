<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Laravel JWT API",
 *     version="1.0.0",
 *     description="API documentation for the Laravel JWT authentication system",
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="Rental API Server"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
