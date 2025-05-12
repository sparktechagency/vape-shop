<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\EmailFiledRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordFiledRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
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


    //login
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $result = $this->authService->login($credentials);

        if ($result == 'invalid_credentials') {
            return response()->errorResponse('Invalid credentials.', 401);
        } elseif ($result == 'email_not_verified') {
            return response()->errorResponse('Email not verified.', 401);
        }
        return response()->successResponse($result, 'Login successful.');
    }


    //verify email
    public function verifyEmail(VerifyEmail $request)
    {
        $otpData = $request->validated();

        $result = $this->authService->verifyEmail($otpData['otp']);

        if ($result['success'] === false) {
            return response()->errorResponse($result['message'], $result['code']);
        }
        return response()->successResponse($result['data'], $result['message']);
    }

    //logout
    public function logout()
    {
        return $this->authService->logout();
    }

    //resend otp
    public function resendOtp(EmailFiledRequest $request)
    {
        $data = $request->validated();
        return $this->authService->resendOtp($data['email']);
    }

    //reset password
    public function resetPassword(PasswordFiledRequest $request)
    {
        $data = $request->validated();

        $result = $this->authService->resetPassword($data);
        if ($result['success'] === false) {
            return response()->errorResponse($result['message'], $result['code']);
        }
        return response()->successResponse($result['data'], $result['message']);
    }

    //update profile

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->authService->updateProfile($data);
            if ($result['success'] === false) {
                return response()->errorResponse($result['message'], $result['code']);
            }
            return response()->successResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to update profile.', 500, $e->getMessage());
        }
    }


    //me
    public function me()
    {
        $result = $this->authService->me();
        if ($result['success'] === false) {
            return response()->errorResponse($result['message'], $result['code']);
        }
        return response()->successResponse($result['data'], $result['message']);
    }



}
