<?php

namespace App\Http\Controllers\admin;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboardController(Request $request)
    {
        //total users
        //total brands
        //total stores
        //total wholesalers
        // total products
        //panding approval

        $totals = User::selectRaw("
            COUNT(*) as totalUsers,
            SUM(role = ?) as totalBrands,
            SUM(role = ?) as totalStores,
            SUM(role = ?) as totalWholesalers
        ", [Role::BRAND, Role::STORE, Role::WHOLESALER])->first();

        $totalUsers = $totals->totalUsers;
        $totalBrands = $totals->totalBrands;
        $totalStores = $totals->totalStores;
        $totalWholesalers = $totals->totalWholesalers;

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalBrands' => $totalBrands,
            'totalStores' => $totalStores,
            'totalWholesalers' => $totalWholesalers,
        ], 200);

    }
}
