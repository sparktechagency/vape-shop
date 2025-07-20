<?php

namespace App\Http\Controllers\admin;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\MostFollowerAd;
use App\Models\TrendingProducts;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {

        $period = $request->input('period', '7days');

        $totals = User::selectRaw("
            COUNT(id) as total_users,
            SUM(role = ?) as total_brands,
            SUM(role = ?) as total_stores,
            SUM(role = ?) as total_wholesalers
        ", [Role::BRAND, Role::STORE, Role::WHOLESALER])->first();

        $currentPeriodStart = Carbon::now()->subDays(7);
        $currentPeriodEnd = Carbon::now();

        $previousPeriodStart = Carbon::now()->subDays(14);
        $previousPeriodEnd = Carbon::now()->subDays(7);


        $newUsersCurrentPeriod = User::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count();

        $newUsersPreviousPeriod = User::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();

        $newBrandsCurrentPeriod = User::where('role', Role::BRAND)
            ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])
            ->count();
        $newBrandsPreviousPeriod = User::where('role', Role::BRAND)
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->count();
        $newStoresCurrentPeriod = User::where('role', Role::STORE)
            ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])
            ->count();
        $newStoresPreviousPeriod = User::where('role', Role::STORE)
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->count();
        $newWholesalersCurrentPeriod = User::where('role', Role::WHOLESALER)
            ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])
            ->count();
        $newWholesalersPreviousPeriod = User::where('role', Role::WHOLESALER)
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->count();


        $newUsersPercentageChange = 0;
        if ($newUsersPreviousPeriod > 0) {

            $newUsersPercentageChange = (($newUsersCurrentPeriod - $newUsersPreviousPeriod) / $newUsersPreviousPeriod) * 100;
        } elseif ($newUsersCurrentPeriod > 0) {

            $newUsersPercentageChange = 100.0;
        }

        //pendign approval brands
        $pendingApprovalProducts = TrendingProducts::where('status', 'pending')
            // ->where('is_active', true)
            ->count();

        $pendingApprovalFollowers = MostFollowerAd::where('status', 'pending')
            // ->where('is_active', true)
            ->count();

        $pendingApproval = $pendingApprovalProducts + $pendingApprovalFollowers;

        return response()->json([
            'ok' => true,
            'message' => 'Dashboard data retrieved successfully.',
            'data' => [
                'Users' => [
                    'count' => $totals->total_users,
                    'percentage_change' => round($newUsersPercentageChange, 2),
                    'direction' => $newUsersPercentageChange >= 0 ? 'increase' : 'decrease',
                ],
                'totalBrands' => [
                    'count' => $totals->total_brands,
                    'percentage_change' => round(($newBrandsCurrentPeriod - $newBrandsPreviousPeriod) / max($newBrandsPreviousPeriod, 1) * 100, 2),
                    'direction' => ($newBrandsCurrentPeriod - $newBrandsPreviousPeriod) >= 0 ? 'increase' : 'decrease',
                ],
                'totalStores' => [
                    'count' => $totals->total_stores,
                    'percentage_change' => round(($newStoresCurrentPeriod - $newStoresPreviousPeriod) / max($newStoresPreviousPeriod, 1) * 100, 2),
                    'direction' => ($newStoresCurrentPeriod - $newStoresPreviousPeriod) >= 0 ? 'increase' : 'decrease',
                ],
                'totalWholesalers' => [
                    'count' => $totals->total_wholesalers,
                    'percentage_change' => round(($newWholesalersCurrentPeriod - $newWholesalersPreviousPeriod) / max($newWholesalersPreviousPeriod, 1) * 100, 2),
                    'direction' => ($newWholesalersCurrentPeriod - $newWholesalersPreviousPeriod) >= 0 ? 'increase' : 'decrease',
                ],
                'pendingApproval' => [
                    'count' => $pendingApproval,
                    'pendingProducts' => $pendingApprovalProducts,
                    'pendingFollowers' => $pendingApprovalFollowers,
                ],
                'userGrowth'=> $this->userGrouth($period),
                'recentActivity' => $this->recentActivity()->original,
            ]
        ], 200);
    }

    private function userGrouth($period)
    {

        $days = match ($period) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            default => 7,
        };

        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();


        $userCounts = User::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date');


        $dateRange = CarbonPeriod::create($startDate, $endDate);

        $labels = [];
        $data = [];

        foreach ($dateRange as $date) {
            $formattedDate = $date->format('Y-m-d');

            $labels[] = $date->format('M j');


            $data[] = $userCounts[$formattedDate]->count ?? 0;
        }


        return response()->json([
                'total_new_users' => array_sum($data),
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'New Users',
                        'data' => $data,
                    ]
                ]
        ]);
    }
    //recent activity -> admin role er notification theke last 3 ta data niye asbe
    private function recentActivity()
    {
        $user = auth()->user();
        $notifications = $user->notifications()
            // ->where('type', 'App\Notifications\Admin\NewTrendingAdRequestNotification')
            // ->orWhere('type', 'App\Notifications\Admin\NewMostFollowerAdRequestNotification')
            ->latest()
            ->take(3)
            ->get();
        if ($notifications->isEmpty()) {
            return response()->error(
                'No recent activity found.',
                404
            );
        }

         return response()->json(
            $notifications
        );

    }

}
