<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Constraint\Count;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $countries = Country::all();
            if ($countries->isEmpty()) {
                return response()->error('No countries found.', 404);
            }
            return response()->success($countries, 'Countries retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while retrieving countries: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        // Check for duplicate country name
        $existingCountry = \App\Models\Country::where('name', $request->input('name'))->first();
        if ($existingCountry) {
            return response()->error('Country with this name already exists.', 409);
        }
        $country = Country::create([
            'name' => $request->input('name'),
        ]);

        return response()->success($country, 'Country created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(country $country)
    {
        return response()->success($country, 'Country retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $country = Country::find($id);
        if (!$country) {
            return response()->error('Country not found.', 404);
        }

        // Check for duplicate country name excluding the current country
        $existingCountry = Country::where('name', $request->input('name'))
            ->where('id', '!=', $id)
            ->first();
        if ($existingCountry) {
            return response()->error('Country with this name already exists.', 409);
        }

        $country->name = $request->input('name');
        $country->save();

        return response()->success($country, 'Country updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->error('Country not found.', 404);
        }

        // Check if the country has associated regions
        $regionCount = $country->regions()->count();
        if ($regionCount > 0) {
            return response()->error('Cannot delete country with associated regions. Please delete the regions first.', 400);
        }

        $country->delete();
        return response()->success(null, 'Country deleted successfully.');
    }
}
