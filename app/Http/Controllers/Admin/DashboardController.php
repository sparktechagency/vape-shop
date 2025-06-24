<?php

namespace App\Http\Controllers\admin;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
     public function dashboard(Request $request)
    {

        $totals = User::selectRaw("
            COUNT(id) as total_users,
            SUM(role = ?) as total_brands,
            SUM(role = ?) as total_stores,
            SUM(role = ?) as total_wholesalers
        ", [Role::BRAND->value, Role::STORE->value, Role::WHOLESALER->value])->first();

        $currentPeriodStart = Carbon::now()->subDays(7);
        $currentPeriodEnd = Carbon::now();

        $previousPeriodStart = Carbon::now()->subDays(14);
        $previousPeriodEnd = Carbon::now()->subDays(7);


        $newUsersCurrentPeriod = User::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count();

        $newUsersPreviousPeriod = User::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();


        $newUsersPercentageChange = 0;
        if ($newUsersPreviousPeriod > 0) {

            $newUsersPercentageChange = (($newUsersCurrentPeriod - $newUsersPreviousPeriod) / $newUsersPreviousPeriod) * 100;
        } elseif ($newUsersCurrentPeriod > 0) {

            $newUsersPercentageChange = 100.0;
        }


        return response()->json([
            'totals' => [
                'users' => $totals->total_users,
                'brands' => (int) $totals->total_brands,
                'stores' => (int) $totals->total_stores,
                'wholesalers' => (int) $totals->total_wholesalers,
            ],
            'statistics' => [
                'new_users' => [
                    'count' => $newUsersCurrentPeriod,
                    'percentage_change' => round($newUsersPercentageChange, 2),
                    'direction' => $newUsersPercentageChange >= 0 ? 'increase' : 'decrease'
                ],

                'active_businesses' => [
                    'count' => (int) ($totals->total_brands + $totals->total_stores + $totals->total_wholesalers),
                    'percentage_change' => 2.5,
                    'direction' => 'increase'
                ],
            ]
        ], 200);
    }
}
