<?php

namespace App\Http\Controllers\Front;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\NearbyStoreResource;
use App\Models\Address;
use App\Models\Branch;
use App\Models\User;
use App\Services\Front\HomeService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class HomeController extends Controller
{
    protected $homeService;

    // Cache configuration
    private const CACHE_TTL = 1800; // 30 minutes
    private const SEARCH_CACHE_PREFIX = 'search';
    private const STORE_BRAND_CACHE_PREFIX = 'store_brand_wholesaler';
    private const PRODUCTS_BY_ROLE_CACHE_PREFIX = 'products_by_role';
    private const STORES_BY_LOCATION_CACHE_PREFIX = 'stores_by_location';

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    /**
     * Generate cache key based on parameters
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $paramString = http_build_query($params);
        return $prefix . '_' . md5($paramString);
    }

    /**
     * Clear all cache related to home controller
     */
    public function clearAllCache(): bool
    {
        return CacheService::clearAll();
    }

    /**
     * Clear specific cache by tags
     */
    public function clearCacheByTags(array $tags): bool
    {
        return CacheService::clearByTags($tags);
    }



    //search brand, shops, products, all account except admin,  and everithing
    public function search(Request $request)
    {
        try {
            $searchTerm = $request->input('search_term', '');
            $type = $request->input('type', 'products'); // default to 'all' if not
            $perPage = $request->input('per_page', 10);
            $regionId = $request->input('region_id', null);
            $page = $request->input('page', 1); // Add page parameter

            // Generate cache key based on search parameters including page
            $cacheKey = $this->generateCacheKey(self::SEARCH_CACHE_PREFIX, [
                'search_term' => $searchTerm,
                'type' => $type,
                'per_page' => $perPage,
                'region_id' => $regionId,
                'page' => $page  // Include page in cache key
            ]);

            // Use cache for search results with tags
            $result = Cache::tags(['search', 'users', 'products', 'stores'])->remember($cacheKey, self::CACHE_TTL, function () use ($searchTerm, $type, $perPage, $regionId) {
                return $this->homeService->search($searchTerm, $type, (int)$perPage, (int)$regionId);
            });

            if (empty($result)) {
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
            $page = $request->input('page', 1); // Add page parameter

            // Generate cache key based on parameters including page
            $cacheKey = $this->generateCacheKey(self::STORE_BRAND_CACHE_PREFIX, [
                'type' => $type,
                'per_page' => $perPage,
                'page' => $page  // Include page in cache key
            ]);

            // Use cache for store/brand/wholesaler data with tags
            $data = Cache::tags(['stores', 'brands', 'wholesalers', 'users'])->remember($cacheKey, self::CACHE_TTL, function () use ($type, $perPage) {
                return $this->homeService->getAllStoreBrandWholesaler($type, $perPage);
            });

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
            $page = $request->input('page', 1); // Add page parameter

            // Generate cache key based on parameters including page
            $cacheKey = $this->generateCacheKey(self::PRODUCTS_BY_ROLE_CACHE_PREFIX, [
                'user_id' => $userId,
                'type' => $type,
                'per_page' => $perPage,
                'page' => $page  // Include page in cache key
            ]);

            // Use cache for products by role data with tags
            $data = Cache::tags(['products', 'users', 'roles'])->remember($cacheKey, self::CACHE_TTL, function () use ($type, $userId, $perPage) {
                return collect($this->homeService->getProductsByRoleId($type, (int)$userId, (int)$perPage));
            });

            if ($data->isEmpty()) {
                return response()->error('No data found', 404);
            }
            return response()->success($data, 'Data retrieved successfully');
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), 500);
        }
    }

    //store maps view
    // public function getStoresByLocation(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'sw_lat' => 'required|numeric|between:-90,90',
    //         'sw_lng' => 'required|numeric|between:-180,180',
    //         'ne_lat' => 'required|numeric|between:-90,90',
    //         'ne_lng' => 'required|numeric|between:-180,180',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $validatedData = $validator->validated();

    //     // Generate cache key based on location parameters
    //     $cacheKey = $this->generateCacheKey(self::STORES_BY_LOCATION_CACHE_PREFIX, [
    //         'sw_lat' => $validatedData['sw_lat'],
    //         'sw_lng' => $validatedData['sw_lng'],
    //         'ne_lat' => $validatedData['ne_lat'],
    //         'ne_lng' => $validatedData['ne_lng']
    //     ]);

    //     // Use cache for stores by location data with tags
    //     $stores = Cache::tags(['stores', 'users', 'locations'])->remember($cacheKey, self::CACHE_TTL, function () use ($validatedData) {
    //         return User::query()
    //             ->with('address')
    //             ->where('role', 5)
    //             ->whereHas('address', function ($query) use ($validatedData) {
    //                 $query->whereNotNull('latitude')
    //                       ->whereNotNull('longitude')
    //                       ->whereBetween('latitude', [$validatedData['sw_lat'], $validatedData['ne_lat']])
    //                       ->whereBetween('longitude', [$validatedData['sw_lng'], $validatedData['ne_lng']]);
    //             })
    //             ->get();
    //     });

    //     return response()->json([
    //         'ok' => true,
    //         'message' => 'Stores retrieved successfully.',
    //         'data' => $stores
    //     ]);
    // }




    public function searchLocations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Radius Search Parameters
            'latitude' => 'required_with:radius,longitude|numeric|between:-90,90',
            'longitude' => 'required_with:radius,latitude|numeric|between:-180,180',
            'radius' => 'sometimes|required_with:latitude,longitude|numeric|min:1',

            // Bounding Box Search Parameters
            'sw_lat' => 'sometimes|required_with:sw_lng,ne_lat,ne_lng|numeric|between:-90,90',
            'sw_lng' => 'sometimes|required_with:sw_lat,ne_lat,ne_lng|numeric|between:-180,180',
            'ne_lat' => 'sometimes|required_with:sw_lat,sw_lng,ne_lng|numeric|between:-90,90',
            'ne_lng' => 'sometimes|required_with:sw_lat,sw_lng,ne_lat|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $baseQuery = Address::query()
            ->where(function ($query) {
                $query->where('addressable_type', Branch::class)
                    ->orWhereHasMorph(
                        'addressable',
                        [User::class],
                        fn($q) => $q->where('role', Role::STORE->value)
                    );
            })
            ->with('addressable');

        if ($request->has('radius')) {
            // --- Radius Search Logic ---
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');


            $radiusInMeters = $request->input('radius', 10000);
            $userLocation = new Point($lat, $lng);

            $locations = $baseQuery
                ->selectRaw(
                    "*, ST_DISTANCE_SPHERE(location, POINT(?, ?)) as distance",
                    [$lng, $lat]
                )
                ->whereDistanceSphere('location', $userLocation, '<=', $radiusInMeters)
                ->orderBy('distance', 'asc')
                ->get();
        } elseif ($request->has('sw_lat')) {
            // --- Bounding Box Search Logic ---
            $sw_lat = $request->sw_lat;
            $sw_lng = $request->sw_lng;
            $ne_lat = $request->ne_lat;
            $ne_lng = $request->ne_lng;

            $boundingBox = new Polygon([
                new LineString([
                    new Point($sw_lat, $sw_lng),
                    new Point($ne_lat, $sw_lng),
                    new Point($ne_lat, $ne_lng),
                    new Point($sw_lat, $ne_lng),
                    new Point($sw_lat, $sw_lng),
                ])
            ]);


            $locationsQuery = $baseQuery->whereWithin('location', $boundingBox);


            $referencePoint = null;
            if ($request->has('latitude') && $request->has('longitude')) {

                $referencePoint = new Point($request->latitude, $request->longitude);
            } else {

                $centerLat = ($sw_lat + $ne_lat) / 2;
                $centerLng = ($sw_lng + $ne_lng) / 2;
                $referencePoint = new Point($centerLat, $centerLng);
            }

            $locationsQuery->withDistance('location', $referencePoint)
                ->orderBy('distance', 'asc');

            $locations = $locationsQuery->get();
        } else {
            return response()->error('Invalid search parameters. Please provide either radius or bounding box coordinates.', 422);
        }

        if ($locations->isEmpty()) {
            return response()->error('No nearby locations found', 404);
        }

        return NearbyStoreResource::collection($locations);
    }
}
