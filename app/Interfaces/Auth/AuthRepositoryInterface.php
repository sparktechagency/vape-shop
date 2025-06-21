<?php

namespace App\Interfaces\Auth;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function register(array $data) : User;
    public function verifyEmail(string $otp) : array;
    public function resendOtp(string $email);
    public function resetPassword($password):array;
    public function updatePassword(array $data):array;
    public function updateProfile(array $data):array;
    public function me($id):array;

}
