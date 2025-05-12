<?php
namespace App\Repositories\Auth;

use App\Enums\UserRole\Role;
use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @param array $data
     * @return User
     */
    public function register(array $data) : User
    {
        $firstName = $this->getFirstNameByRole($data['role'], $data);
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


    /**
     * @param string $role
     * @param array $data
     * @return string
     */
    private function getFirstNameByRole($role, $data) : string
    {
        switch ($role) {
            case Role::STORE->value:
                return $data['store_name'];
            case Role::BRAND->value:
                return $data['brand_name'];
            default:
                return $data['first_name'];
        }
    }

    /**
     * @param string $otp
     * @return array
     */
    public function verifyEmail(string $otp) : array
    {
        $user = User::where('otp', $otp)->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid OTP.',
                'code' => 422,
            ];
        }
        if (Carbon::parse($user->otp_expire_at)->isPast()) {
            return [
                'success' => false,
                'message' => 'OTP expired.',
                'code' => 422,
            ];
        }
        $user->email_verified_at = now();
        $user->otp = null;
        $user->otp_expire_at = null;
        $user->save();

        return [
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => $user,
        ];
    }

    public function resendOtp(string $email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->errorResponse('User not found.', 404);
        }
        $otp_data = [
            'name' => $user->first_name,
            'email' => $user->email,
        ];
        $otp_data = sentOtp($otp_data, 5);
        $user->otp = $otp_data['otp'];
        $user->otp_expire_at = $otp_data['otp_expire_at'];
        $user->save();

        return response()->successResponse(null, 'OTP sent successfully! Please check your email!');
    }

    //reset password
    public function resetPassword($password):array

    {
       $user = Auth::user();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'code' => 404,
            ];
        }
        $user->password = $password;
        $user->save();

        return [
            'success' => true,
            'message' => 'Password reset successfully.',
            'code' => 200,
        ];
    }

    //update password
    public function updatePassword(array $data):array
    {
        $user = Auth::user();
        if(Hash::check($data['current_password'], $user->password)) {
            $user->password = Hash::make($data['new_password']);
            $user->save();
        } else {
            return [
                'success' => false,
                'message' => 'Current password is incorrect.',
                'code' => 422,
            ];
        }

        return [
            'success' => true,
            'message' => 'Password updated successfully.',
            'code' => 200,
        ];
    }

    //update profile
    public function updateProfile(array $data) : array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'code' => 404,
            ];
        }
        $firstName = $this->getFirstNameByRole($user->role, $data);
        $user->first_name = $firstName;
        $user->last_name = $data['last_name'] ?? $user->last_name;
        $user->phone = $data['phone'] ?? $user->phone;
        $user->address = $data['address'] ?? $user->address;
        $user->zip_code = $data['zip_code'] ?? $user->zip_code;
        $user->region = $data['region'] ?? $user->region;
        $user->save();

        return [
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user,
            'code' => 200,
        ];
    }

    public function me() : array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'code' => 404,
            ];
        }
        return [
            'success' => true,
            'message' => 'User retrieved successfully.',
            'data' => $user,
            'code' => 200,
        ];
    }
}
