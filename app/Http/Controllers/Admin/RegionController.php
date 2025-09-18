<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $regions = Region::all();
            if ($regions->isEmpty()) {
                return response()->error('No regions found.', 404);
            }
            return response()->success($regions, 'Regions retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while retrieving regions: ' . $e->getMessage(), 500);
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
        // try{
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|integer|exists:countries,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $region = new Region();
        $region->country_id = $request->input('country_id');
        $region->name = $request->input('name');
        $region->code = Str::upper($request->input('code'));
        $region->save();
        //forget cache for regions
        cache()->forget('all_countries_with_regions');
        cache()->forget("country_{$region->country_id}_regions");
        return response()->success($region, 'Region created successfully.');
        // } catch (\Exception $e) {
        //     return response()->error('An error occurred while creating the region: ' . $e->getMessage(), 500);
        // }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $region = Region::find($id);
            if (!$region) {
                return response()->error('Region not found.', 404);
            }
            return response()->success($region, 'Region retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while retrieving the region: ' . $e->getMessage(), 500);
        }
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
        try {
            $validator = Validator::make($request->all(), [
                'country_id' => 'sometimes|integer|exists:countries,id',
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }

            $region = Region::find($id);
            if (!$region) {
                return response()->error('Region not found.', 404);
            }

            if ($request->has('country_id')) {
                $region->country_id = $request->input('country_id');
            }
            if ($request->has('name')) {
                $region->name = $request->input('name');
            }
            if ($request->has('code')) {
                $region->code = Str::upper($request->input('code'));
            }
            $region->save();
            // Forget cache for regions
            cache()->forget('all_countries_with_regions');
            cache()->forget("country_{$region->country_id}_regions");
            return response()->success($region, 'Region updated successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while updating the region: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // $categoryIds = Region::orderBy('id')->limit(63)->pluck('id')->toArray();

            // if (in_array($id, $categoryIds)) {
            //     return response()->error('This region cannot be deleted.', 403);
            // }

            $region = Region::find($id);
            if (!$region) {
                return response()->error('Region not found.', 404);
            }

            // Forget cache for regions
            cache()->forget('all_countries_with_regions');
            cache()->forget("country_{$region->country_id}_regions");
            $region->delete();
            return response()->success(null, 'Region deleted successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while deleting the region: ' . $e->getMessage(), 500);
        }
    }
}
