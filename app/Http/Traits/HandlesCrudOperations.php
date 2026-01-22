<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

trait HandlesCrudOperations
{
    /**
     * Handle CRUD operation with proper error handling.
     */
    protected function handleCrudOperation(
        callable $operation,
        string $successMessage,
        string $errorContext,
        ?string $redirectRoute = null
    ): RedirectResponse|JsonResponse {
        try {
            $result = $operation();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => $result,
                ], 200);
            }

            $redirect = $redirectRoute ? redirect()->route($redirectRoute) : back();

            return $redirect->with('success', $successMessage);

        } catch (ValidationException $e) {
            Log::warning("Validation error in {$errorContext}", [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();

        } catch (ModelNotFoundException $e) {
            Log::warning("Resource not found in {$errorContext}", [
                'exception' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }

            return back()->with('error', 'Resource not found')->withInput();

        } catch (Throwable $e) {
            Log::error("Error in {$errorContext}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    /**
     * Handle bulk operation with proper error handling.
     */
    protected function handleBulkOperation(
        callable $operation,
        string $successMessage,
        string $errorContext
    ): RedirectResponse|JsonResponse {
        try {
            $result = $operation();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => $result,
                ], 200);
            }

            return back()->with('success', $successMessage);

        } catch (Throwable $e) {
            Log::error("Bulk operation error in {$errorContext}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk operation failed. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->with('error', 'Bulk operation failed. Please try again.');
        }
    }

    /**
     * Handle soft delete with cascade options.
     */
    protected function handleDelete(
        callable $operation,
        string $successMessage = 'Record deleted successfully',
        string $errorContext = 'delete operation'
    ): RedirectResponse|JsonResponse {
        return $this->handleCrudOperation(
            $operation,
            $successMessage,
            $errorContext
        );
    }
}
