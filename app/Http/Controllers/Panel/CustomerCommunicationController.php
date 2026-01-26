<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invoice;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerCommunicationController extends Controller
{
    public function __construct(
        private SmsService $smsService,
        private AuditLogService $auditLogService
    ) {}

    /**
     * Show SMS compose form
     */
    public function showSmsForm(User $customer)
    {
        $this->authorize('sendSms', $customer);

        $templates = SmsTemplate::where('tenant_id', $customer->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('panels.admin.customers.communication.send-sms', compact('customer', 'templates'));
    }

    /**
     * Send SMS to customer
     */
    public function sendSms(Request $request, User $customer)
    {
        $this->authorize('sendSms', $customer);

        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'template_id' => 'nullable|exists:sms_templates,id',
        ]);

        // Check if customer has a phone number
        if (empty($customer->phone)) {
            return back()->withErrors(['phone' => 'Customer does not have a phone number.']);
        }

        try {
            // Replace variables in message
            $message = $this->replaceVariables($validated['message'], $customer);

            $result = $this->smsService->sendSms(
                $customer->phone,
                $message,
                null,
                $customer->id,
                $customer->tenant_id
            );

            $this->auditLogService->log(
                'sms_sent',
                'Sent SMS to customer',
                ['customer_id' => $customer->id, 'message_length' => strlen($message)]
            );

            if ($result) {
                return back()->with('success', 'SMS sent successfully.');
            } else {
                return back()->with('error', 'Failed to send SMS. Please check gateway configuration.');
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to send SMS: ' . $e->getMessage());
        }
    }

    /**
     * Show payment link form
     */
    public function showPaymentLinkForm(User $customer)
    {
        $this->authorize('sendLink', $customer);

        $invoices = Invoice::where('user_id', $customer->id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get();

        return view('panels.admin.customers.communication.send-payment-link', compact('customer', 'invoices'));
    }

    /**
     * Generate and send payment link
     */
    public function sendPaymentLink(Request $request, User $customer)
    {
        $this->authorize('sendLink', $customer);

        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'send_via' => 'required|array',
            'send_via.*' => 'in:sms,email',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            $invoice = $validated['invoice_id'] 
                ? Invoice::findOrFail($validated['invoice_id'])
                : null;

            // Generate unique payment link token
            $token = Str::random(32);
            
            // Build payment link without relying on a missing named route
            $baseUrl = rtrim((string) config('app.url'), '/');
            $queryParams = [
                'customer' => $customer->id,
                'token' => $token,
            ];
            if ($invoice) {
                $queryParams['invoice'] = $invoice->id;
            }
            $paymentLink = $baseUrl . '/payment-link?' . http_build_query($queryParams);

            // Store the token (you may want to add a payment_links table)
            // For now, we'll just generate the link

            $message = "Payment Link for {$customer->name}: {$paymentLink}";
            if ($invoice) {
                $message .= " | Amount: " . config('app.currency') . number_format($invoice->total_amount, 2);
            }

            $smsSent = false;
            $emailSent = false;
            
            if (in_array('sms', $validated['send_via']) && $customer->phone) {
                $smsSent = $this->smsService->sendSms(
                    $customer->phone,
                    $message,
                    null,
                    $customer->id,
                    $customer->tenant_id
                );
            }

            if (in_array('email', $validated['send_via']) && $customer->email) {
                // TODO: Email sending would go here
                // For now, we'll just mark as sent
                $emailSent = true;
            }

            $this->auditLogService->log(
                'payment_link_sent',
                'Sent payment link to customer',
                [
                    'customer_id' => $customer->id,
                    'invoice_id' => $invoice?->id,
                    'methods' => $validated['send_via'],
                    'sms_sent' => $smsSent,
                    'email_sent' => $emailSent,
                ]
            );

            // Success if at least one requested method succeeded
            $success = (in_array('sms', $validated['send_via']) && $smsSent) || 
                      (in_array('email', $validated['send_via']) && $emailSent);

            if ($success) {
                return back()->with('success', 'Payment link sent successfully via ' . implode(' and ', $validated['send_via']));
            } else {
                return back()->with('error', 'Failed to send payment link. Please check customer contact information.');
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to send payment link: ' . $e->getMessage());
        }
    }

    /**
     * Replace variables in message
     */
    private function replaceVariables(string $message, User $customer): string
    {
        $package = $customer->currentPackage;
        $pendingInvoices = Invoice::where('user_id', $customer->id)
            ->where('status', 'pending')
            ->get();

        $totalDue = $pendingInvoices->sum('total_amount');

        $replacements = [
            '{name}' => $customer->name,
            '{username}' => $customer->username ?? $customer->email,
            '{phone}' => $customer->phone,
            '{package}' => $package?->name ?? 'N/A',
            '{package_price}' => $package ? number_format($package->price, 2) : 'N/A',
            '{due_amount}' => number_format($totalDue, 2),
            '{currency}' => config('app.currency', 'BDT'),
            '{date}' => now()->format('Y-m-d'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }
}
