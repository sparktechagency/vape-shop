<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Front\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $homeService;
    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }


    public function getAllStoreBrandWholesaler(Request $request)
    {
        try {
            $type = $request->input('type');
            $perPage = $request->input('per_page', 12);
            $data = $this->homeService->getAllStoreBrandWholesaler($type);
            if ($data->isEmpty()) {
                return response()->error('No data found', 404);
            }
            return response()->success($data, 'Data retrieved successfully');
        } catch (\Exception $e) {
            return response()->error('Something went wrong.', 500, $e->getMessage());
        }
    }

    public function getProductsByRoleId(Request $request, $userId)
    {
        try {
            // $storeId = $request->input('store_id');
            // return $userId;
            $perPage = $request->input('per_page', 10);
            $type = $request->input('type');
            $data = $this->homeService->getProductsByRoleId($type, (int)$userId, (int)$perPage);
            return $data;
            if ($data->isEmpty()) {
                return response()->error('No data found', 404);
            }
            return response()->success($data, 'Data retrieved successfully');
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), 500);
        }
    }


}
