<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Notifications\AdminSendNotificationToUser;
use App\Notifications\UserSuspendedNotification;
use App\Services\Auth\AuthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function manageUsers(Request $request)
    {
        $role = (int)$request->role;
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $query = match ($role) {
            Role::MEMBER->value => User::withoutGlobalScope('active'),
            Role::STORE->value => User::withoutGlobalScope('active'),
            Role::BRAND->value => User::withoutGlobalScope('active'),
            Role::WHOLESALER->value => User::withoutGlobalScope('active'),
            Role::ASSOCIATION->value => User::withoutGlobalScope('active'),
            default => User::withoutGlobalScope('active')->where('role', '!=', Role::ADMIN->value)
        };

        if (in_array($role, [Role::MEMBER->value, Role::STORE->value, Role::BRAND->value, Role::WHOLESALER->value, Role::ASSOCIATION->value])) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ein', 'like', "%{$search}%");
            });
        }

        $users = $query->with([
            'address.region.country',
            'favourites',
            'subscriptions' => function ($q) {
                $q->select('id', 'invoice_status', 'ends_at', 'subscribable_id', 'subscribable_type');
            }
        ])->latest()->paginate($perPage);

        if ($users->isEmpty()) {
            return response()->error('No users found for the specified role.', 404);
        }

        return UserResource::collection($users);
    }

    public function getUserById($id)
    {
        // Note: Ensure AuthService also handles withoutGlobalScope internally if it uses Eloquent
        $authService = new AuthService(app(AuthRepositoryInterface::class));
        $user = $authService->me($id, true); // true indicates to bypass global scopes
        return response()->success($user, 'User retrieved successfully.');
    }

    public function banUser(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }

            $banUser = User::withoutGlobalScope('active')->find($id);

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
            $banUser = User::withoutGlobalScope('active')
                ->where('id', $id)
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

    public function getBannedUsers(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $bannedUsers = User::withoutGlobalScope('active')
            ->whereNotNull('banned_at')
            ->paginate($perPage);

        if ($bannedUsers->isEmpty()) {
            return response()->error('No banned users found.', 404);
        }
        return response()->success($bannedUsers, 'Banned users retrieved successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::withoutGlobalScope('active')->find($id);

        if (!$user) {
            return response()->error('User not found', 404);
        }
        if ($user->delete()) {
            return response()->success(null, 'User deleted successfully.');
        } else {
            return response()->error('Failed to delete user', 500);
        }
    }

    public function suspend(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = User::withoutGlobalScope('active')->find($id);

        if (!$user) {
            return response()->error('User not found', 404);
        }

        if ($user->isSuspended()) {
            return response()->error("User '{$user->full_name}' is already suspended.", 400);
        }
        $days = (int)$request->input('days');
        $user->suspended_at = now();
        $user->suspended_until = now()->addDays($days);
        $user->suspend_reason = $request->input('reason');
        $user->save();

        $user->notify(new UserSuspendedNotification($user->suspend_reason, $user->suspended_until));

        $data = [
            'suspended_until' => $user->suspended_until->toDateTimeString(),
            'reason' => $user->suspend_reason,
        ];
        return response()->success($data, "User '{$user->full_name}' has been suspended for {$days} days.");
    }

    public function suspendedUsers()
    {
        $suspendedUsers = User::withoutGlobalScope('active')
            ->where('suspended_until', '>', now())
            ->whereNotNull('suspended_at')
            ->get();

        $data = $suspendedUsers->map(function ($user) {
            $suspendedAt = $user->suspended_at;
            $suspendedUntil = $user->suspended_until;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'suspension_reason' => $user->suspend_reason,
                'suspended_at' => $suspendedAt->toDateTimeString(),
                'suspension_ends_at' => $suspendedUntil->toDateTimeString(),
                'total_suspension_days' => $suspendedAt->diffInDays($suspendedUntil),
                'days_remaining' => intval(Carbon::now()->diffInDays($suspendedUntil, false) + 1),
            ];
        });

        if ($data->isEmpty()) {
            return response()->error('No suspended users found.', 404);
        }
        return response()->success($data, 'Suspended users retrieved successfully.');
    }

    public function unsuspend($id)
    {
        $user = User::withoutGlobalScope('active')->find($id);

        if (!$user) {
            return response()->error('User not found', 404);
        }

        if (!$user->isSuspended()) {
            return response()->error("User '{$user->full_name}' is not suspended.", 400);
        }
        $user->suspended_at = null;
        $user->suspended_until = null;
        $user->suspend_reason = null;
        $user->save();

        return response()->success(null, "Suspension has been lifted for user '{$user->full_name}'.");
    }

    public function sendNotification(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $user = User::withoutGlobalScope('active')->find($id);

        if (!$user) {
            return response()->error('User not found', 404);
        }

        $user->notify(new AdminSendNotificationToUser(
            $request->input('title'),
            $request->input('description')
        ));

        return response()->success(null, "Notification sent successfully to '{$user->full_name}'.");
    }
}
