<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = (int) request()->input('role');
        $user = Auth::user();
        if (!$user) {
            return response()->error('Unauthorized', 401);
        }
        if(!in_array($role, [Role::BRAND, Role::STORE])) {
            return response()->error('Invalid role specified.', 422);
        }
        if($role){
            $favourites = $user->favourites()->select('users.id', 'users.first_name', 'users.role','users.avatar')->where('role', $role)->get();
        }

        $favourites->makeHidden(['total_followers','first_name', 'total_following', 'email', 'password', 'remember_token']);

        if ($favourites->isEmpty()) {
            return response()->error('No favourites found.', 404);
        }

        return response()->success($favourites, 'Favourites retrieved successfully.');
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
            'favourite_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $favouriteId = $request->input('favourite_id');
        $favoriteUser = \App\Models\User::find($favouriteId);

        if (Auth::id() == $favouriteId) {
            return response()->error('You cannot favourite yourself.', 422);
        }

        if (!$favoriteUser || !in_array($favoriteUser->role, [Role::BRAND, Role::STORE])) {
            return response()->error('Invalid favorite user.', 422);
        }

        $user = Auth::user();

        // Check if the favorite relationship already exists using exists()
        $isAlreadyFavorited = $user->favourites()->where('users.id', $favouriteId)->exists();

        if ($isAlreadyFavorited) {
            // If exists then remove it
            $user->favourites()->detach($favouriteId);
            return response()->success(null, 'Favorite removed successfully.');
        } else {
            // If not exists then add it
            $user->favourites()->attach($favouriteId);
            return response()->success(null, 'Favorite added successfully.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function destroy(string $id)
    {
        //
    }
}
