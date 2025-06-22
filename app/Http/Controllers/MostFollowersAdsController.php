<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Requests\MostFollowersAdRequest;
use App\Models\MostFollowerAd;
use App\Models\User;
use App\Notifications\MostFollowersRequestConfirmation;
use App\Notifications\NewMostFollowersAdRequestNotification;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MostFollowersAdsController extends Controller
{

    protected $paymentService;
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $mostFollowersAds = MostFollowerAd::with(['user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        if ($mostFollowersAds->isEmpty()) {
            return response()->error(
                'No most followers ads found.',
                404
            );
        }
        return response()->success(
            $mostFollowersAds,
            'Most followers ads retrieved successfully.'
        );
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
    public function store(MostFollowersAdRequest $request)
    {
        DB::beginTransaction();
        try {

            // dd(TrendingProducts::with(['product'])->get());
            // Validate the request data
            $user = Auth::user();
            $validatedData = $request->validated();

            // Create a new trending ad product using the validated data
            $mostFollowersAdRequest = MostFollowerAd::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'preferred_duration' => $validatedData['preferred_duration'],
                'requested_at' => now(),
            ]);

            $mostFollowersAdRequest->amount = $validatedData['amount'];


            $response = $this->paymentService->processPaymentForPayable($mostFollowersAdRequest, $validatedData);
            // Return a success response
            if ($response['status'] === 'success') {
                //send notification to admin
                $admins = User::where('role', Role::ADMIN->value)->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new NewMostFollowersAdRequestNotification($mostFollowersAdRequest));
                }

                // Optionally, you can notify the user as well
                $user->notify(new MostFollowersRequestConfirmation($mostFollowersAdRequest));
                DB::commit();
                return response()->success(
                    $mostFollowersAdRequest,
                    $response['message'] ?? 'Most follower ad request submitted successfully.',
                    201
                );
            }
            DB::rollBack();
            return response()->error(
                $response['message'] ?? 'Failed to create most follower ad request.',
                422
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->error(
                'An error occurred while creating most followre ad request: ' . $e->getMessage(),
                500
            );
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
