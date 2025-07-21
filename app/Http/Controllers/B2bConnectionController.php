<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\B2bConnection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class B2bConnectionController extends Controller
{

    //send b2b connection request
    public function sendRequest(User $provider)
    {
        if (Auth::id() === $provider->id) {
            return response()->error(
                'You cannot send a request to yourself.',
                400,
                'Invalid Request'
            );
        }
        // Check if a request already exists
        $existingConnection = B2bConnection::where('requester_id', Auth::id())
            ->where('provider_id', $provider->id)
            ->first();
        if ($existingConnection) {
            return response()->error(
                'You have already sent a request to this provider.',
                400,
                'Request Already Exists'
            );
        }
        if ($provider->role !== Role::BRAND->value && $provider->role !== Role::WHOLESALER->value && $provider->role !== Role::STORE->value) {
            return response()->error(
                'Invalid provider role. Only brands, wholesalers, and stores can be providers.',
                400,
                'Invalid Provider'
            );
        }
        $requester = Auth::user();
        B2bConnection::updateOrCreate(
            ['requester_id' => $requester->id, 'provider_id' => $provider->id],
            ['status' => 'pending']
        );

        return response()->success(
            ['provider' => $provider, 'requester' => $requester],
            'B2B connection request sent successfully.'
        );
    }

    //update b2b connection request status
    public function updateRequest(Request $request, B2bConnection $connection)
    {
        if (Auth::id() !== $connection->provider_id) {
            return response()->error(
                'You are not authorized to update this request.',
                403,
                'Unauthorized'
            );
        }
        $validated = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validated->fails()) {
            return response()->error(
                $validated->errors()->first(),
                422,
                $validated->errors()
            );
        }
        $validated = $validated->validated();
        // if ($validated['status'] === 'rejected') {
        //     $connection->delete();
        //     return response()->json(['message' => 'B2B request has been rejected.']);
        // }

        $connection->update(['status' => $validated['status']]);
        //notification logic can be added here
        return response()->success(
            $connection,
            'B2B connection request status updated successfully.'
        );
    }
    public function listIncoming(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 15); // Default to 15 items per page

        $columns = [
            'b2b_connections.id',
            'users.id as user_id',
            'users.first_name',
            'users.last_name',
            'users.role',
            'users.avatar'
        ];


        $requests = Auth::user()
            ->b2bRequesters()
            ->paginate($perPage, $columns);

        return response()->success(
            $requests,
            'Incoming B2B connection requests fetched successfully.'
        );
        }catch(\Exception $e) {
            return response()->error(
                'Failed to fetch incoming requests.',
                500,
                $e->getMessage()
            );
        }
    }
}
