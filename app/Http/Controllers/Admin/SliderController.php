<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\FileUploadTrait;

class SliderController extends Controller
{
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = Slider::all();
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
        $slider = Slider::find($id);
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
            return response()->success(null, 'Slider deleted successfully.');
        } else {
            return response()->error('Failed to delete slider.', 500);
        }
    }
}
