<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Customs\Services\PasswordService;
use App\Http\Requests\ChangePassordRequest;

class PasswordController extends Controller
{
    public function __construct(private PasswordService $service)
    {
        
    }
    public function changeUserPassword(ChangePassordRequest $request)
    {
        return $this->service->changePassword($request->validated());
    }
}
