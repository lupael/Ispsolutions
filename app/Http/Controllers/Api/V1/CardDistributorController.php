<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Card;
use App\Models\CardSale;
use App\Services\DistributorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * CardDistributorController
 * 
 * Note: Updated to use User model (operator_level = 100) instead of NetworkUser model.
 */
class CardDistributorController extends Controller
{
    protected $distributorService;
    
    public function __construct(DistributorService $distributorService)
    {
        $this->middleware('throttle:distributor-api');
        $this->distributorService = $distributorService;
    }
    
    /**
     * Get list of mobile numbers for a distributor
     * 
     * @group Distributor API
     * @authenticated
     * 
     * @queryParam country_code string Filter by country code. Example: BD
     * @queryParam status string Filter by status (active/inactive). Example: active
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Items per page (max 100). Example: 50
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "mobiles": [
     *       {
     *         "id": 1,
     *         "mobile": "+8801712345678",
     *         "country_code": "BD",
     *         "status": "active",
     *         "balance": 1500.00,
     *         "last_recharge": "2026-01-20 10:30:00"
     *       }
     *     ],
     *     "pagination": {
     *       "current_page": 1,
     *       "per_page": 50,
     *       "total": 150,
     *       "last_page": 3
     *     }
     *   }
     * }
     */
    public function getMobiles(Request $request)
    {
        $distributor = $request->user();
        
        $cacheKey = "distributor:{$distributor->id}:mobiles:" . md5(json_encode($request->all()));
        
        $data = Cache::remember($cacheKey, 600, function () use ($request, $distributor) {
            $query = User::where('distributor_id', $distributor->id)
                ->where('role', 'customer');
            
            // Apply filters
            if ($request->has('country_code')) {
                $query->where('country_code', $request->country_code);
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $perPage = min($request->input('per_page', 50), 100);
            $mobiles = $query->paginate($perPage);
            
            return [
                'mobiles' => $mobiles->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'mobile' => $this->distributorService->formatMobile($customer->mobile, $customer->country_code),
                        'country_code' => $customer->country_code,
                        'status' => $customer->status,
                        'balance' => $customer->balance,
                        'last_recharge' => $customer->last_payment_date,
                    ];
                }),
                'pagination' => [
                    'current_page' => $mobiles->currentPage(),
                    'per_page' => $mobiles->perPage(),
                    'total' => $mobiles->total(),
                    'last_page' => $mobiles->lastPage(),
                ],
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    
    /**
     * Get available cards for sale
     * 
     * @group Distributor API
     * @authenticated
     * 
     * @queryParam package_id integer Filter by package. Example: 5
     * @queryParam status string Filter by status (available/sold). Example: available
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "cards": [
     *       {
     *         "id": 100,
     *         "serial": "CARD-2026-00100",
     *         "pin": "123456",
     *         "package_id": 5,
     *         "package_name": "1GB Daily",
     *         "price": 50.00,
     *         "validity_days": 30,
     *         "status": "available"
     *       }
     *     ],
     *     "summary": {
     *       "total_available": 500,
     *       "total_value": 25000.00
     *     }
     *   }
     * }
     */
    public function getCards(Request $request)
    {
        $distributor = $request->user();
        
        $cacheKey = "distributor:{$distributor->id}:cards:" . md5(json_encode($request->all()));
        
        $data = Cache::remember($cacheKey, 300, function () use ($request, $distributor) {
            $query = Card::where('distributor_id', $distributor->id);
            
            // Apply filters
            if ($request->has('package_id')) {
                $query->where('package_id', $request->package_id);
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $cards = $query->with('package')->get();
            
            return [
                'cards' => $cards->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'serial' => $card->serial,
                        'pin' => $card->pin,
                        'package_id' => $card->package_id,
                        'package_name' => $card->package->name,
                        'price' => $card->price,
                        'validity_days' => $card->validity_days,
                        'status' => $card->status,
                    ];
                }),
                'summary' => [
                    'total_available' => $cards->where('status', 'available')->count(),
                    'total_value' => $cards->where('status', 'available')->sum('price'),
                ],
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    
    /**
     * Get sales history
     * 
     * @group Distributor API
     * @authenticated
     * 
     * @queryParam from_date string Filter sales from date (Y-m-d). Example: 2026-01-01
     * @queryParam to_date string Filter sales to date (Y-m-d). Example: 2026-01-31
     * @queryParam mobile string Filter by mobile number. Example: +8801712345678
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "sales": [
     *       {
     *         "id": 500,
     *         "mobile": "+8801712345678",
     *         "card_serial": "CARD-2026-00100",
     *         "package_name": "1GB Daily",
     *         "price": 50.00,
     *         "sold_at": "2026-01-20 14:30:00"
     *       }
     *     ],
     *     "summary": {
     *       "total_sales": 150,
     *       "total_revenue": 7500.00,
     *       "date_range": {
     *         "from": "2026-01-01",
     *         "to": "2026-01-31"
     *       }
     *     }
     *   }
     * }
     */
    public function getSales(Request $request)
    {
        $distributor = $request->user();
        
        $query = CardSale::where('distributor_id', $distributor->id);
        
        // Apply date filters
        if ($request->has('from_date')) {
            $query->whereDate('sold_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('sold_at', '<=', $request->to_date);
        }
        
        // Apply mobile filter
        if ($request->has('mobile')) {
            $normalizedMobile = $this->distributorService->normalizeMobile($request->mobile);
            $query->where('mobile', $normalizedMobile);
        }
        
        $sales = $query->with(['card.package'])->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'mobile' => $sale->mobile,
                        'card_serial' => $sale->card->serial,
                        'package_name' => $sale->card->package->name,
                        'price' => $sale->price,
                        'sold_at' => $sale->sold_at,
                    ];
                }),
                'summary' => [
                    'total_sales' => $sales->count(),
                    'total_revenue' => $sales->sum('price'),
                    'date_range' => [
                        'from' => $request->from_date ?? 'N/A',
                        'to' => $request->to_date ?? 'N/A',
                    ],
                ],
            ],
        ]);
    }
    
    /**
     * Record a new card sale
     * 
     * @group Distributor API
     * @authenticated
     * 
     * @bodyParam mobile string required Mobile number with country code. Example: +8801712345678
     * @bodyParam card_id integer required Card ID to sell. Example: 100
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Card sold successfully",
     *   "data": {
     *     "sale_id": 501,
     *     "mobile": "+8801712345678",
     *     "card_serial": "CARD-2026-00100",
     *     "package_name": "1GB Daily",
     *     "price": 50.00,
     *     "validity_days": 30,
     *     "expires_at": "2026-02-20"
     *   }
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "Card not available",
     *   "errors": {
     *     "card_id": ["The selected card is not available for sale"]
     *   }
     * }
     */
    public function recordSale(Request $request)
    {
        $validated = $request->validate([
            'mobile' => 'required|string',
            'card_id' => 'required|exists:cards,id',
        ]);
        
        $distributor = $request->user();
        
        // Normalize mobile number
        $normalizedMobile = $this->distributorService->normalizeMobile($validated['mobile']);
        
        // Validate mobile format
        if (!$this->distributorService->validateMobile($normalizedMobile)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number format',
                'errors' => ['mobile' => ['Mobile number must be in international format']],
            ], 400);
        }
        
        // Check card availability
        $card = Card::where('id', $validated['card_id'])
            ->where('distributor_id', $distributor->id)
            ->where('status', 'available')
            ->first();
        
        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not available',
                'errors' => ['card_id' => ['The selected card is not available for sale']],
            ], 400);
        }
        
        // Record sale
        $sale = CardSale::create([
            'distributor_id' => $distributor->id,
            'card_id' => $card->id,
            'mobile' => $normalizedMobile,
            'price' => $card->price,
            'sold_at' => now(),
        ]);
        
        // Update card status
        $card->update(['status' => 'sold']);
        
        // Activate customer account if exists
        $customer = User::where('mobile', $normalizedMobile)->first();
        $expiresAt = now()->addDays($card->validity_days);
        
        if ($customer) {
            // Update user's package and expiry (User model with operator_level = 100)
            $customer->update([
                'service_package_id' => $card->package_id,
                'expiry_date' => $expiresAt,
                'status' => 'active',
            ]);
        }
        
        // Clear cache
        if (config('cache.default') === 'redis' || config('cache.default') === 'memcached') {
            Cache::tags("distributor:{$distributor->id}")->flush();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Card sold successfully',
            'data' => [
                'sale_id' => $sale->id,
                'mobile' => $normalizedMobile,
                'card_serial' => $card->serial,
                'package_name' => $card->package->name,
                'price' => $card->price,
                'validity_days' => $card->validity_days,
                'expires_at' => $expiresAt->format('Y-m-d'),
            ],
        ], 201);
    }
}
