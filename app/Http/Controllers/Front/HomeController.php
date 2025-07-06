<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Front\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $homeService;
    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }



    //search brand, shops, products, all account except admin,  and everithing
    public function search(Request $request)
    {
        try {
            $searchTerm = $request->input('search_term', '');
            $type = $request->input('type', 'products'); // default to 'all' if not
            $perPage = $request->input('per_page', 10);
            $regionId = $request->input('region_id', null);

            $result = $this->homeService->search($searchTerm, $type, (int)$perPage, (int)$regionId);
            if ($result->isEmpty()) {
                return response()->error('No data found', 404);
            }
            return response()->success($result, 'Data retrieved successfully');
        } catch (\InvalidArgumentException $e) {
            return response()->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return response()->error('Something went wrong.', 500, $e->getMessage());
        }
    }

    public function getAllStoreBrandWholesaler(Request $request)
    {
        try {
            $type = $request->input('type');
            $perPage = $request->input('per_page', 12);
            $data = $this->homeService->getAllStoreBrandWholesaler($type, $perPage);
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
            $data = collect($this->homeService->getProductsByRoleId($type, (int)$userId, (int)$perPage));
            // return $data;
            if ($data->isEmpty()) {
                return response()->error('No data found', 404);
            }
            return response()->success($data, 'Data retrieved successfully');
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), 500);
        }
    }


}
