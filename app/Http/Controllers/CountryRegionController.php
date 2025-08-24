<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CountryRegionController extends Controller
{
    // Cache TTL
    private const CACHE_TTL = 86400; // 24 hours - Country/Region data rarely changes

    //get all countries
    public function getAllCountries()
    {
        $cacheKey = 'all_countries_with_regions';
        $countries = Cache::tags(['countries', 'locations'])->remember($cacheKey, self::CACHE_TTL, function () {
            return Country::with('regions')->get();
        });

        if ($countries->isEmpty()) {
            return response()->error('No countries found.', 404);
        }
        return response()->success($countries, 'Countries retrieved successfully.');
    }

    //get regions by country ID
    public function getRegionsByCountryId($countryId)
    {
        $cacheKey = "country_{$countryId}_regions";
        $regions = Cache::tags(['countries', 'locations'])->remember($cacheKey, self::CACHE_TTL, function () use ($countryId) {
            $country = Country::with('regions')->find($countryId);
            return $country ? $country->regions : null;
        });

        if (is_null($regions)) {
            return response()->error('Country not found.', 404);
        }

        if ($regions->isEmpty()) {
            return response()->error('No regions found for this country.', 404);
        }

        return response()->success($regions, 'Regions retrieved successfully.');
    }
}
