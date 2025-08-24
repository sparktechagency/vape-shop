<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Traits\FileUploadTrait;

class SliderController extends Controller
{
    use FileUploadTrait;

    // Cache key for sliders
    private const SLIDERS_CACHE_KEY = 'sliders_list';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Clear all slider-related cache using CacheService
     */
    private function clearSliderCache($sliderId = null)
    {
        // Clear specific slider tags
        CacheService::clearByTags(['sliders', 'home']);

        if ($sliderId) {
            CacheService::clearByTag("slider_{$sliderId}");
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = Cache::tags(['sliders', 'home'])->remember(self::SLIDERS_CACHE_KEY, self::CACHE_TTL, function () {
            return Slider::all();
        });

        if($sliders->isEmpty()){
            return response()->error('No sliders found.', 404);
        }
        return response()->success($sliders, 'Sliders retrieved successfully.');
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:3072',
        ]);
        if($validator->fails()){
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        //maximum 8 slider allowed
        $sliderCount = Slider::count();
        if($sliderCount >= 20){
            return response()->error('Maximum 20 sliders are allowed.', 422);
        }

        $image = $request->file('image');

        if($image){
            // $imagePath = $image->store('sliders', 'public');
            $imagePath = $this->handleFileUpload(
                $request,
                'image',
                'sliders',
                1920, // width
                1080, // height
                85, // quality
                true // forceWebp
            );

        }

        $slider = new Slider();
        $slider->image = $imagePath;
        $slider->save();

        if($slider){
            // Clear cache when new slider is created
            $this->clearSliderCache();
            return response()->success($slider, 'Slider created successfully.');
        } else {
            return response()->error('Failed to create slider.', 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cacheKey = "slider_{$id}";
        $slider = Cache::tags(['sliders', 'home'])->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Slider::find($id);
        });

        if(!$slider){
            return response()->error('Slider not found.', 404);
        }
        return response()->success($slider, 'Slider retrieved successfully.');
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
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:3072',
        ]);
        if($validator->fails()){
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $slider = Slider::find($id);
        if(!$slider){
            return response()->error('Slider not found.', 404);
        }

        //remove old image if exists
        if($slider->image && $request->hasFile('image')){
            $oldImagePath = getStorageFilePath($slider->image);
            if($oldImagePath && Storage::disk('public')->exists($oldImagePath)){
                Storage::disk('public')->delete($oldImagePath);
            }
        }


        if($request->hasFile('image')){
            $image = $request->file('image');
            // $imagePath = $image->store('sliders', 'public');
            $imagePath = $this->handleFileUpload(
                $request,
                'image',
                'sliders',
                1920, // width
                1080, // height
                85, // quality
                true // forceWebp
            );
            $slider->image = $imagePath;
        }

        if($slider->save()){
            // Clear cache when slider is updated
            $this->clearSliderCache($id);
            return response()->success($slider, 'Slider updated successfully.');
        } else {
            return response()->error('Failed to update slider.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = Slider::find($id);
        if(!$slider){
            return response()->error('Slider not found.', 404);
        }

        //remove old image if exists
        if($slider->image){
            $oldImagePath = getStorageFilePath($slider->image);
            if($oldImagePath && Storage::disk('public')->exists($oldImagePath)){
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        if($slider->delete()){
            // Clear cache when slider is deleted
            $this->clearSliderCache($id);
            return response()->success(null, 'Slider deleted successfully.');
        } else {
            return response()->error('Failed to delete slider.', 500);
        }
    }
}
