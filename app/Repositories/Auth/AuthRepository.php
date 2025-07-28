<?php

namespace App\Repositories\Auth;

use App\Enums\UserRole\Role;
use App\Http\Resources\FavouriteUserResource;
use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Models\Address;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $firstName = $this->getFirstNameByRole($data['role'], $data);
        $otp_data = [
            'name' => $firstName,
            'email' => $data['email'],
        ];
        $otp_data = sentOtp($otp_data, 5);
        $user = new User();
        $user->first_name = $firstName;
        $user->last_name = (int) $data['role'] === Role::MEMBER->value ? $data['last_name'] : null;
        $user->dob = $data['dob'] ?? null;
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->phone = $data['phone'] ?? null;
        $user->role = $data['role'];
        $user->otp = $otp_data['otp'];
        $user->otp_expire_at = $otp_data['otp_expire_at'];
        $user->ein = $data['ein'] ?? null;
        $user->save();

        $address = new Address();
        $address->user_id = $user->id;
        $address->region_id = $data['region_id'] ?? null;
        $address->address = $data['address'] ?? null;
        $address->zip_code = $data['zip_code'] ?? null;
        $address->latitude = $data['latitude'] ?? null;
        $address->longitude = $data['longitude'] ?? null;
        $address->save();

        $user->load('address');
        return $user;
    }


    /**
     * @param string $role
     * @param array $data
     * @return string
     */
    private function getFirstNameByRole($role, $data): string
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
    public function verifyEmail(string $otp): array
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
            return response()->error('User not found.', 404);
        }
        $otp_data = [
            'name' => $user->first_name,
            'email' => $user->email,
        ];
        $otp_data = sentOtp($otp_data, 5);
        $user->otp = $otp_data['otp'];
        $user->otp_expire_at = $otp_data['otp_expire_at'];
        $user->save();

        return response()->success(null, 'OTP sent successfully! Please check your email!');
    }

    //reset password
    public function resetPassword($password): array

    {
        $user = Auth::user();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'code' => 404,
            ];
        }
        $user->password = password_hash((string) $password['password'], PASSWORD_DEFAULT);
        $user->save();

        return [
            'success' => true,
            'message' => 'Password reset successfully.',
            'code' => 200,
        ];
    }

    //update password
    public function updatePassword(array $data): array
    {
        $user = Auth::user();
        if (Hash::check($data['current_password'], $user->password)) {
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
    public function updateProfile(array $data): array
    {
        $user = Auth::user();
        $avatar = request()->file('avatar');
        $coverPhoto = request()->file('cover_photo');
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'code' => 404,
            ];
        }

        if ($avatar) {
            $oldImagePath = getStorageFilePath($user->avatar);
            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
            $user->avatar = $avatar->store('avatars', 'public');
        }

        //cover photo
        if ($coverPhoto) {
            $oldImagePath = getStorageFilePath($user->cover_photo);
            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
            $user->cover_photo = $coverPhoto->store('cover_photos', 'public');
        }



        $firstName = $this->getFirstNameByRole($user->role, $data);
        $user->first_name = $firstName;
        $user->last_name = $data['last_name'] ?? $user->last_name;
        $user->phone = $data['phone'] ?? $user->phone;
        $user->dob = $data['dob'] ?? $user->dob;
        $user->ein = $data['ein'] ?? $user->ein;
        $user->pl = $data['pl'] ?? $user->pl;
        if ($user->role == Role::STORE->value) {
            $user->open_from = $data['open_from'];
            $user->close_at = $data['close_at'];
        }
        $user->save();

        // Update or create address for the same user id
        $address = $user->address()->firstOrNew([]); // address property is polymorphic now
        $address->region_id = $data['region_id'] ?? $address->region_id;
        $address->address = $data['address'] ?? $address->address;
        $address->zip_code = $data['zip_code'] ?? $address->zip_code;
        $address->latitude = $data['latitude'] ?? $address->latitude;
        $address->longitude = $data['longitude'] ?? $address->longitude;
        $address->save();

        return [
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user,
            'code' => 200,
        ];
    }

    public function me($id): array
    {

        $user = User::where('id', $id)->first();

        $user->load('address.region', 'favourites', 'about');


        [$favouriteStores, $favouriteBrands] = $user->favourites->partition(function ($favoriteUser) {
            return $favoriteUser->role === Role::STORE->value;
        });
        // $user = Auth::user()->load('address.region', 'favourites');
        unset($user->favourites);
        // dd($favouriteStores, $favouriteBrands);

        $user->favourite_store_list = FavouriteUserResource::collection($favouriteStores->values());
        $user->favourite_brand_list = FavouriteUserResource::collection($favouriteBrands->values());
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

    //get user by id

}
