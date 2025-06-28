<?php

namespace App\Http\Controllers;

use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AboutController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $userId = $request->user_id ?? Auth::user()->id;
            $page = About::where('user_id', $userId)->first();

            if ($page) {
                return response()->success(
                    $page,
                    'About page content retrieved successfully.',
                    200
                );
            } else {
                return response()->error(
                    'No about page content found for this user.',
                    404
                );
            }
        } catch (\Exception $th) {
            return response()->error(
                'Error retrieving about page content',
                500,
                $th->getMessage()
            );
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
        try {
            $user = Auth::user();
            $validated = Validator::make($request->all(), [
                'content' => 'required|string'
            ]);

            if ($validated->fails()) {
                return response()->error(
                    $validated->errors()->first(),
                    422,
                    $validated->errors()
                );
            }

            $page = About::updateOrCreate(
                ['user_id' => $user->id],
                ['content' => $request->content]
            );

            return response()->success(
                $page,
                'About page content updated successfully.',
                200
            );
        } catch (\Exception $th) {
            return response()->error(
                'Error updating about page content',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy()
    // {
    //     try {
    //         $user = Auth::user();
    //         $page = About::where('user_id', $user->id)->first();

    //         if (!$page) {
    //             return response()->error(
    //                 'About page not found for this user.',
    //                 404
    //             );
    //         }

    //         $page->delete();

    //         return response()->success(
    //             null,
    //             'About page deleted successfully.',
    //             200
    //         );
    //     } catch (\Exception $th) {
    //         return response()->error(
    //             'Error deleting about page content',
    //             500,
    //             $th->getMessage()
    //         );
    //     }
    // }
}
