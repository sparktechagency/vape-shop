<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\EmailFiledRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordFiledRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $user = $this->authService->register($data);
            DB::commit();
            return response()->success($user, 'User registered successfully. Please verify your email.');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->error('Failed to register user.', 500, $e->getMessage());
        }
    }


    //login
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $result = $this->authService->login($credentials);

        if ($result == 'invalid_credentials') {
            return response()->error('Invalid credentials.', 401);
        } elseif ($result == 'email_not_verified') {
            return response()->error('Email not verified.', 401);
        }
        return response()->success($result, 'Login successful.');
    }


    //verify email
    public function verifyEmail(VerifyEmail $request)
    {
        $otpData = $request->validated();

        $result = $this->authService->verifyEmail($otpData['otp']);

        if ($result['success'] === false) {
            return response()->error($result['message'], $result['code']);
        }
        return response()->success($result['data'], $result['message']);
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
            return response()->error($result['message'], $result['code']);
        }
        return response()->success(null, $result['message']);
    }

    //update password
    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->authService->updatePassword($data);
            if ($result['success'] === false) {
                return response()->error($result['message'], $result['code']);
            }
            return response()->success(null, $result['message']);
        } catch (\Exception $e) {
            return response()->error('Failed to update password.', 500, $e->getMessage());
        }
    }

    //update profile
    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->authService->updateProfile($data);
            if ($result['success'] === false) {
                return response()->error($result['message'], $result['code']);
            }
            return response()->success($result['data'], $result['message']);
        } catch (\Exception $e) {
            return response()->error('Failed to update profile.', 500, $e->getMessage());
        }
    }

    //get user by id



    //me
    public function me()
    {
        $userId = Auth::id();
        $result = $this->authService->me($userId);
        if ($result['success'] === false) {
            return response()->error($result['message'], $result['code']);
        }
        return response()->success($result['data'], $result['message']);
    }

     public function profile($id)
    {
        $result = $this->authService->me($id);
        if ($result['success'] === false) {
            return response()->error($result['message'], $result['code']);
        }
        return response()->success($result['data'], $result['message']);
    }
}
