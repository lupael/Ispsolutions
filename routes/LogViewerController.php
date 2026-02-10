<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    /**
     * Display the device operations log.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function deviceOperations()
    {
        $logFile = storage_path('logs/device_operations.log');
        $logs = [];

        if (File::exists($logFile)) {
            // Read the file and reverse the array to show the latest logs first
            $logContent = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

            foreach ($logContent as $line) {
                // Basic parsing to make the log more readable
                if (preg_match('/^\[(.*?)\] \w+\.(\w+): (.*)/s', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'level' => strtolower($matches[2]),
                        'message' => $matches[3],
                    ];
                }
            }
        }

        // Since the layout is not provided, we assume a standard admin layout file.
        // You may need to adjust 'layouts.admin' to your actual layout file, e.g., 'layouts.app'.
        return view('admin.logs.device_operations', ['logs' => $logs]);
    }
}
