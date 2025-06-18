<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function manageUsers(Request $request)
    {
        $role = (int)$request->role;
        $perPage = $request->input('per_page', 10);
        $users = match ($role) {
            Role::MEMBER->value => User::where('role', Role::MEMBER->value)->paginate($perPage),
            Role::STORE->value => User::where('role', Role::STORE->value)->paginate($perPage),
            Role::BRAND->value => User::where('role', Role::BRAND->value)->paginate($perPage),
            Role::WHOLESALER->value => User::where('role', Role::WHOLESALER->value)->paginate($perPage),
            Role::ASSOCIATION->value => User::where('role', Role::ASSOCIATION->value)->paginate($perPage),
            default => User::where('role', '!=', Role::ADMIN->value)->paginate($perPage)
        };

        if ($users->isEmpty()) {
            return response()->error('No users found for the specified role.', 404);
        }
        return response()->success($users, 'Users retrieved successfully.');
    }

    //get user information by ID
    public function getUserById($id)
    {
        $user = User::where('role','!=',Role::ADMIN)->find($id);
        if (!$user) {
            return response()->error('User not found.', 404);
        }
        return response()->success($user, 'User retrieved successfully.');
    }
}
