<?php

namespace Database\Seeders;

use App\Models\SmsEvent;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SmsEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants or create for first tenant if none exist
        $tenants = Tenant::all();
        
        // If no tenants exist, create without tenant_id (for single-tenant setups)
        if ($tenants->isEmpty()) {
            $this->createSmsEvents(null);
            return;
        }
        
        // Create SMS events for each tenant
        foreach ($tenants as $tenant) {
            $this->createSmsEvents($tenant->id);
        }
    }
    
    /**
     * Create SMS events for a tenant
     */
    private function createSmsEvents(?int $tenantId): void
    {
        // Bill Generated Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'bill_generated',
            'event_label' => 'Bill Generated',
            'message_template' => 'Dear {customer_name}, your bill of {bill_amount} has been generated. Invoice: {invoice_number}. Due date: {due_date}. Thank you!',
            'is_active' => true,
            'available_variables' => ['customer_name', 'bill_amount', 'due_date', 'invoice_number'],
        ]);

        // Payment Received Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'payment_received',
            'event_label' => 'Payment Received',
            'message_template' => 'Dear {customer_name}, we have received your payment of {payment_amount}. Receipt: {receipt_number}. Thank you!',
            'is_active' => true,
            'available_variables' => ['customer_name', 'payment_amount', 'payment_date', 'receipt_number'],
        ]);

        // Package Expiring Soon Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'package_expiring',
            'event_label' => 'Package Expiring Soon',
            'message_template' => 'Dear {customer_name}, your {package_name} will expire in {days_remaining} days on {expiry_date}. Please renew to avoid service interruption.',
            'is_active' => true,
            'available_variables' => ['customer_name', 'package_name', 'expiry_date', 'days_remaining'],
        ]);

        // Package Expired Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'package_expired',
            'event_label' => 'Package Expired',
            'message_template' => 'Dear {customer_name}, your {package_name} has expired on {expired_date}. Please renew your package to restore service.',
            'is_active' => true,
            'available_variables' => ['customer_name', 'package_name', 'expired_date'],
        ]);

        // Account Suspended Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'account_suspended',
            'event_label' => 'Account Suspended',
            'message_template' => 'Dear {customer_name}, your account has been suspended. Reason: {suspension_reason}. Please contact support.',
            'is_active' => true,
            'available_variables' => ['customer_name', 'suspension_reason', 'suspension_date'],
        ]);

        // Account Activated Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'account_activated',
            'event_label' => 'Account Activated',
            'message_template' => 'Dear {customer_name}, your account has been activated with {package_name}. Welcome!',
            'is_active' => true,
            'available_variables' => ['customer_name', 'activation_date', 'package_name'],
        ]);

        // Welcome Message Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'welcome_message',
            'event_label' => 'Welcome Message',
            'message_template' => 'Welcome {customer_name}! Your account is ready. Username: {username}, Password: {password}, Package: {package_name}. Enjoy!',
            'is_active' => true,
            'available_variables' => ['customer_name', 'username', 'password', 'package_name'],
        ]);

        // Data Limit Reached Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'data_limit_reached',
            'event_label' => 'Data Limit Reached',
            'message_template' => 'Dear {customer_name}, you have reached your data limit of {data_limit}. Current usage: {usage}. Resets on: {reset_date}.',
            'is_active' => true,
            'available_variables' => ['customer_name', 'data_limit', 'usage', 'reset_date'],
        ]);

        // Time Limit Reached Event
        SmsEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => 'time_limit_reached',
            'event_label' => 'Time Limit Reached',
            'message_template' => 'Dear {customer_name}, you have reached your time limit. Your session will be disconnected. Resets on: {reset_date}.',
            'is_active' => false,
            'available_variables' => ['customer_name', 'time_limit', 'usage', 'reset_date'],
        ]);
    }
}
