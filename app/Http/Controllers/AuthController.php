<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmail;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    //register
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = $this->authService->register($data);

        return response()->successResponse($user, 'User registered successfully. Please verify your email.');
    }


    //verify email
    public function verifyEmail(VerifyEmail $request)
    {
        $otpData = $request->validated();

        $data = $this->authService->verifyEmail($otpData['otp']);

        if($data == 'invalid') {
            return response()->errorResponse('Invalid OTP.', 422);
        } elseif($data == 'expired') {
            return response()->errorResponse('OTP expired.', 422);
        }

        return response()->successResponse($data, 'Email verified successfully.');
    }

}
