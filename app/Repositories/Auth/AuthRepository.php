<?php
namespace App\Repositories\Auth;

use App\Enums\UserRole\Role;
use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $data)
    {
        $firstName = null;
        if(Role::STORE->value == $data['role']) {
            $firstName = $data['store_name'];
        } elseif(Role::BRAND->value == $data['role']) {
            $firstName = $data['brand_name'];
        } else {
            $firstName = $data['first_name'];
        }

        $otp_data = [
            'name' => $firstName,
            'email' => $data['email'],
        ];
        $otp_data = sentOtp($otp_data, 5);
        $user = new User();
        $user->first_name = $firstName;
        $user->last_name = $data['last_name'] ?? null;
        $user->dob = $data['dob'] ?? null;
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->phone = $data['phone'] ?? null;
        $user->address = $data['address'] ?? null;
        $user->zip_code = $data['zip_code'] ?? null;
        $user->region = $data['region'] ?? null;
        $user->role = $data['role'];
        $user->otp = $otp_data['otp'];
        $user->otp_expire_at = $otp_data['otp_expire_at'];
        $user->save();

        return $user;
    }

    public function login(array $credentials)
    {
        // Implement the login logic here
        // For example, authenticate the user with the provided credentials
    }

    public function verifyEmail(string $otp)
    {
        $user = User::where('otp', $otp)->first();
        if (!$user) {
            return 'invalid';
        }
        if ($user->otp_expire_at < now()) {
            return 'expired';
        }
        $user->email_verified_at = now();
        $user->otp = null;
        $user->otp_expire_at = null;
        $user->save();

        return $user;
    }

    public function resendOtp(string $email)
    {
        // Implement the logic to resend OTP here
        // For example, generate a new OTP and send it to the user's email
    }

    public function logout()
    {
        // Implement the logout logic here
        // For example, invalidate the user's session or token
    }

    public function refreshToken()
    {
        // Implement the token refresh logic here
        // For example, generate a new token for the user
    }

    public function me()
    {
        // Implement the logic to get the authenticated user's information here
        // For example, return the user's profile data
    }
}
