<?php

namespace App\Http\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

trait HandlesFormValidation
{
    /**
     * Handle a form submission with error handling.
     *
     * @param callable $callback The operation to perform
     * @param string $successMessage Success message to display
     * @param string $errorContext Context for logging errors
     * @param string|null $redirectRoute Route to redirect to on success
     * @return RedirectResponse
     */
    protected function handleFormSubmission(
        callable $callback,
        string $successMessage,
        string $errorContext,
        ?string $redirectRoute = null
    ): RedirectResponse {
        try {
            $result = $callback();
            
            $redirect = $redirectRoute 
                ? redirect()->route($redirectRoute) 
                : back();
                
            return $redirect->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error("{$errorContext}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Operation failed. Please try again or contact support.');
        }
    }

    /**
     * Handle bulk operations with error handling.
     *
     * @param array $ids IDs to process
     * @param callable $callback Operation to perform on each item
     * @param string $successMessage Success message template
     * @param string $errorContext Context for logging
     * @return RedirectResponse
     */
    protected function handleBulkOperation(
        array $ids,
        callable $callback,
        string $successMessage,
        string $errorContext
    ): RedirectResponse {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                $callback($id);
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "ID {$id}: " . $e->getMessage();
                Log::error("{$errorContext} failed for ID {$id}: " . $e->getMessage());
            }
        }

        if ($failedCount === 0) {
            return back()->with('success', sprintf($successMessage, $successCount));
        } elseif ($successCount === 0) {
            return back()->with('error', "All operations failed. Please check the logs.");
        } else {
            return back()->with('warning', sprintf(
                "Partial success: %d succeeded, %d failed.",
                $successCount,
                $failedCount
            ));
        }
    }
}
