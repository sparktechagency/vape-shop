<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavouriteUserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
//use authcontroller
use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function manageUsers(Request $request)
    {
        $role = (int)$request->role;
        $perPage = $request->input('per_page', 10);

        $query = match ($role) {
            Role::MEMBER->value => User::where('role', Role::MEMBER->value),
            Role::STORE->value => User::where('role', Role::STORE->value),
            Role::BRAND->value => User::where('role', Role::BRAND->value),
            Role::WHOLESALER->value => User::where('role', Role::WHOLESALER->value),
            Role::ASSOCIATION->value => User::where('role', Role::ASSOCIATION->value),
            default => User::where('role', '!=', Role::ADMIN->value)
        };

        $users = $query->with('favourites')->latest()->paginate($perPage);

        if ($users->isEmpty()) {
            return response()->error('No users found for the specified role.', 404);
        }


        return UserResource::collection($users);
    }

    //get user information by ID
    public function getUserById($id)
    {
        $authService = new AuthService(app(AuthRepositoryInterface::class));
        $user = $authService->me($id);
        return response()->success($user, 'User retrieved successfully.');
    }

    //ban user
    public function banUser(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }
            $banUser = User::find($id);
            if (!$banUser) {
                return response()->error('User not found', 404);
            }
            $banUser->banned_at = now();
            $banUser->ban_reason = $request->input('reason');
            $banUser->save();

            return response()->success(null, 'User banned successfully.');
        } catch (\Exception $e) {
            return response()->error('Failed to ban user', 500, $e->getMessage());
        }
    }
    public function unBanUser($id)
    {
        try {
            $banUser = User::where('id', $id)
                ->whereNotNull('banned_at')
                ->first();
            if (!$banUser) {
                return response()->error('User not found', 404);
            }
            $banUser->banned_at = null;
            $banUser->ban_reason = null;
            $banUser->save();

            return response()->success(null, 'User unbanned successfully.');
        } catch (\Exception $e) {
            return response()->error('Failed to unban user', 500, $e->getMessage());
        }
    }

    //get all banned users
    public function getBannedUsers(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $bannedUsers = User::whereNotNull('banned_at')->paginate($perPage);
        if ($bannedUsers->isEmpty()) {
            return response()->error('No banned users found.', 404);
        }
        return response()->success($bannedUsers, 'Banned users retrieved successfully.');
    }
}
