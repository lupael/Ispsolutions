<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\IpamServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\IpAllocation;
use App\Models\IpPool;
use App\Models\IpSubnet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IpamController extends Controller
{
    public function __construct(
        private readonly IpamServiceInterface $ipamService
    ) {}

    /**
     * List all IP pools
     */
    public function listPools(Request $request): JsonResponse
    {
        $query = IpPool::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $pools = $query->with('subnets')->paginate($request->get('per_page', 15));

        return response()->json($pools);
    }

    /**
     * Create a new IP pool
     */
    public function createPool(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ip_pools',
            'description' => 'nullable|string',
            'start_ip' => 'required|ip',
            'end_ip' => 'required|ip',
            'gateway' => 'nullable|ip',
            'dns_servers' => 'nullable|string',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pool = IpPool::create($validator->validated());

        return response()->json([
            'message' => 'IP pool created successfully',
            'data' => $pool,
        ], 201);
    }

    /**
     * Get a specific IP pool
     */
    public function getPool(int $id): JsonResponse
    {
        $pool = IpPool::with(['subnets', 'subnets.allocations'])->findOrFail($id);

        return response()->json($pool);
    }

    /**
     * Update an IP pool
     */
    public function updatePool(Request $request, int $id): JsonResponse
    {
        $pool = IpPool::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:ip_pools,name,' . $id,
            'description' => 'nullable|string',
            'start_ip' => 'sometimes|ip',
            'end_ip' => 'sometimes|ip',
            'gateway' => 'nullable|ip',
            'dns_servers' => 'nullable|string',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pool->update($validator->validated());

        return response()->json([
            'message' => 'IP pool updated successfully',
            'data' => $pool,
        ]);
    }

    /**
     * Delete an IP pool
     */
    public function deletePool(int $id): JsonResponse
    {
        $pool = IpPool::findOrFail($id);

        // Check if pool has active allocations
        if ($pool->subnets()->whereHas('allocations', function ($query) {
            $query->where('status', 'allocated');
        })->exists()) {
            return response()->json([
                'message' => 'Cannot delete pool with active allocations',
            ], 400);
        }

        $pool->delete();

        return response()->json([
            'message' => 'IP pool deleted successfully',
        ]);
    }

    /**
     * List all IP subnets
     */
    public function listSubnets(Request $request): JsonResponse
    {
        $query = IpSubnet::with('pool');

        if ($request->has('pool_id')) {
            $query->where('pool_id', $request->pool_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $subnets = $query->paginate($request->get('per_page', 15));

        return response()->json($subnets);
    }

    /**
     * Create a new IP subnet
     */
    public function createSubnet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pool_id' => 'required|exists:ip_pools,id',
            'network' => 'required|ip',
            'prefix_length' => 'required|integer|min:8|max:32',
            'gateway' => 'nullable|ip',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Check for subnet overlap
        if ($this->ipamService->detectOverlap($data['network'], $data['prefix_length'])) {
            return response()->json([
                'message' => 'Subnet overlaps with existing subnet',
            ], 400);
        }

        $subnet = IpSubnet::create($data);

        return response()->json([
            'message' => 'IP subnet created successfully',
            'data' => $subnet->load('pool'),
        ], 201);
    }

    /**
     * Get a specific IP subnet
     */
    public function getSubnet(int $id): JsonResponse
    {
        $subnet = IpSubnet::with(['pool', 'allocations'])->findOrFail($id);

        return response()->json($subnet);
    }

    /**
     * Update an IP subnet
     */
    public function updateSubnet(Request $request, int $id): JsonResponse
    {
        $subnet = IpSubnet::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'gateway' => 'nullable|ip',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $subnet->update($validator->validated());

        return response()->json([
            'message' => 'IP subnet updated successfully',
            'data' => $subnet,
        ]);
    }

    /**
     * Delete an IP subnet
     */
    public function deleteSubnet(int $id): JsonResponse
    {
        $subnet = IpSubnet::findOrFail($id);

        // Check if subnet has active allocations
        if ($subnet->allocations()->where('status', 'allocated')->exists()) {
            return response()->json([
                'message' => 'Cannot delete subnet with active allocations',
            ], 400);
        }

        $subnet->delete();

        return response()->json([
            'message' => 'IP subnet deleted successfully',
        ]);
    }

    /**
     * List all IP allocations
     */
    public function listAllocations(Request $request): JsonResponse
    {
        $query = IpAllocation::with(['subnet', 'subnet.pool']);

        if ($request->has('subnet_id')) {
            $query->where('subnet_id', $request->subnet_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('username')) {
            $query->where('username', $request->username);
        }

        $allocations = $query->paginate($request->get('per_page', 15));

        return response()->json($allocations);
    }

    /**
     * Allocate an IP address
     */
    public function allocateIP(Request $request): JsonResponse
    {
        // MAC address validation pattern
        $macAddressPattern = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';

        $validator = Validator::make($request->all(), [
            'subnet_id' => 'required|exists:ip_subnets,id',
            'mac_address' => ['required', 'string', 'regex:' . $macAddressPattern],
            'username' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $allocation = $this->ipamService->allocateIP(
            $data['subnet_id'],
            $data['mac_address'],
            $data['username']
        );

        if (! $allocation) {
            return response()->json([
                'message' => 'Failed to allocate IP address. Subnet may be full or inactive.',
            ], 400);
        }

        return response()->json([
            'message' => 'IP address allocated successfully',
            'data' => $allocation->load('subnet'),
        ], 201);
    }

    /**
     * Release an IP address
     */
    public function releaseIP(int $id): JsonResponse
    {
        $success = $this->ipamService->releaseIP($id);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to release IP address. Allocation may not exist.',
            ], 400);
        }

        return response()->json([
            'message' => 'IP address released successfully',
        ]);
    }

    /**
     * Get pool utilization statistics
     */
    public function getPoolUtilization(int $id): JsonResponse
    {
        $pool = IpPool::findOrFail($id);

        $utilization = $this->ipamService->getPoolUtilization($id);

        return response()->json([
            'pool' => $pool,
            'utilization' => $utilization,
        ]);
    }

    /**
     * Get available IPs in a subnet
     */
    public function getAvailableIPs(int $id): JsonResponse
    {
        $subnet = IpSubnet::findOrFail($id);

        $availableIPs = $this->ipamService->getAvailableIPs($id);

        return response()->json([
            'subnet' => $subnet,
            'available_ips' => $availableIPs,
            'count' => count($availableIPs),
        ]);
    }
}
