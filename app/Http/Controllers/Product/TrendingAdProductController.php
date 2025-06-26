<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Models\TrendingProducts;
use App\Http\Requests\Product\TrendingAdProductRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TrendingAdProductResource;
use App\Models\User;
use App\Notifications\NewTrendingAdRequestNotification;
use App\Notifications\TrendingRequestConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TrendingAdProductController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware(['jwt.auth', 'check.role:' . Role::BRAND->value])->except(['index', 'show']);
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $trendingAdProducts = TrendingProducts::with(['product','payments'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        if ($trendingAdProducts->isEmpty()) {
            return response()->error(
                'No trending ad products found.',
                404
            );
        }
        return response()->success(
            TrendingAdProductResource::collection($trendingAdProducts),
            'Trending ad products retrieved successfully.'
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
    public function store(TrendingAdProductRequest $request)
    {
        DB::beginTransaction();
        try {

            // dd(TrendingProducts::with(['product'])->get());
            // Validate the request data
            $user = Auth::user();
            $validatedData = $request->validated();

            // Create a new trending ad product using the validated data
            $trendingAdProduct = TrendingProducts::create([
                'user_id' => $user->id,
                'product_id' => $validatedData['product_id'],
                // 'amount' => $validatedData['amount'],
                'status' => 'pending',
                'preferred_duration' => $validatedData['preferred_duration'],
                'requested_at' => now(),
            ]);

            $trendingAdProduct->amount = $validatedData['amount'];


            $response = $this->paymentService->processPaymentForPayable($trendingAdProduct, $validatedData);
            // Return a success response
            if ($response['status'] === 'success') {
                //send notification to admin
                $admins = User::where('role', Role::ADMIN->value)->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new NewTrendingAdRequestNotification($trendingAdProduct));
                }

                // Optionally, you can notify the user as well
                $user->notify(new TrendingRequestConfirmation($trendingAdProduct));
                DB::commit();
                return response()->success(
                    $trendingAdProduct,
                    $response['message'] ?? 'Trending ad product created successfully.',
                    201
                );
            }
            DB::rollBack();
            return response()->error(
                $response['message'] ?? 'Failed to create trending ad product.',
                422
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->error(
                'An error occurred while creating the trending ad product: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $trendingAdProduct = TrendingProducts::with(['product','payments'])
            ->where('id', $id)
            ->first();

        if (!$trendingAdProduct) {
            return response()->error(
                'Trending ad product not found.',
                404
            );
        }

        return response()->success(
            new TrendingAdProductResource($trendingAdProduct),
            'Trending ad product retrieved successfully.'
        );
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
