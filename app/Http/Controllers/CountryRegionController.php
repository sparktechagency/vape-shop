<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryRegionController extends Controller
{
    //get all countries
    public function getAllCountries()
    {
        $countries = Country::with('regions')->get(); // Assuming 'regions' is a relationship defined in the Country model
        if ($countries->isEmpty()) {
            return response()->error('No countries found.', 404);
        }
        return response()->success($countries, 'Countries retrieved successfully.');
    }

    //get regions by country ID
    public function getRegionsByCountryId($countryId){
        $country = Country::with('regions')->find($countryId);
        if (!$country) {
            return response()->error('Country not found.', 404);
        }

        $regions = $country->regions; // Assuming 'regions' is a relationship defined in the Country model

        if ($regions->isEmpty()) {
            return response()->error('No regions found for this country.', 404);
        }

        return response()->success($regions, 'Regions retrieved successfully.');
    }
}
