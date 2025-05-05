<?php

namespace App\Interfaces\Auth;

interface AuthRepositoryInterface
{
    public function register(array $data);
    public function login(array $credentials);
    public function verifyEmail(string $otp);
    public function resendOtp(string $email);
    public function logout();
    public function refreshToken();
    public function me();
}
