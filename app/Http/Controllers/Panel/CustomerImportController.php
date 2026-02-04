use App\Events\ImportPppCustomersRequested;
use App\Http\Controllers\Controller;
use App\Models\CustomerImport;
use App\Models\MasterPackage;
use App\Models\MikrotikRouter;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerImportController extends Controller
{
    /**
     * Show import form.
     */
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $user = auth()->user();
        
        // Get active routers (NAS functionality is now integrated into routers)
        $routers = MikrotikRouter::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();
            
        $packagesQuery = MasterPackage::where('status', 'active');
        if ($user->operator_level === \App\Models\User::OPERATOR_LEVEL_SUPER_ADMIN || $user->operator_level === \App\Models\User::OPERATOR_LEVEL_ADMIN) {
            $packagesQuery->where(function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id)
                  ->orWhereNull('tenant_id'); // Include global packages
            });
        }
        $packages = $packagesQuery->get();

        // Get recent imports
        $recentImports = CustomerImport::where('operator_id', auth()->id())
            ->with(['nas', 'router'])
            ->latest()
            ->take(10)
            ->get();

        return view('panels.admin.customers.pppoe-import', compact('routers', 'packages', 'recentImports'));
    }

    /**
     * Start import process.
     */
    public function store(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        
        $validated = $request->validate([
            'router_id' => [
                'required',
                'integer',
                'exists:mikrotik_routers,id',
            ],
            'filter_disabled' => 'nullable|boolean',
            'generate_bills' => 'nullable|boolean',
            'package_id' => [
                'nullable',
                'integer',
                'exists:master_packages,id',
            ],
        ]);

        try {
            // Verify router belongs to tenant and is active (security check)
            $router = MikrotikRouter::where('id', $validated['router_id'])
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->first();

            if (!$router) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid router selected. Router must be active and belong to your tenant.',
                ], 422);
            }

            // Verify package belongs to tenant if provided (security check)
            if (!empty($validated['package_id'])) {
                $package = MasterPackage::where('id', $validated['package_id'])
                    ->first();

                if (!$package) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid package selected.',
                    ], 422);
                }
            }

            // Check for duplicate import today
            $existingImport = CustomerImport::where('operator_id', auth()->id())
                ->whereDate('created_at', today())
                ->where('status', 'in_progress')
                ->where('router_id', $validated['router_id'])
                ->first();

            if ($existingImport) {
                return response()->json([
                    'success' => false,
                    'message' => 'An import is already in progress for this router today.',
                ], 422);
            }

            // Dispatch event (NAS functionality is now part of routers)
            event(new ImportPppCustomersRequested(
                auth()->id(),
                null, // nas_id deprecated
                [
                    'router_id' => $validated['router_id'],
                    'filter_disabled' => $validated['filter_disabled'] ?? true,
                    'generate_bills' => $validated['generate_bills'] ?? false,
                    'package_id' => $validated['package_id'] ?? null,
                ]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Import process has been started. You will be notified when it completes.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
