<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table indexes
        // Note: Only adding indexes for columns that exist in the users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'idx_users_email');
            // username column does not exist in users table - removed
            $table->index('tenant_id', 'idx_users_tenant_id');
            $table->index(['tenant_id', 'is_active'], 'idx_users_tenant_active');
        });

        // Network users table indexes
        Schema::table('network_users', function (Blueprint $table) {
            $table->index('username', 'idx_network_users_username');
            $table->index('tenant_id', 'idx_network_users_tenant_id');
            $table->index('status', 'idx_network_users_status');
            $table->index('service_type', 'idx_network_users_service_type');
            $table->index(['tenant_id', 'status'], 'idx_network_users_tenant_status');
            $table->index(['tenant_id', 'service_type'], 'idx_network_users_tenant_service');
        });

        // Invoices table indexes
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_invoices_tenant_id');
            $table->index('user_id', 'idx_invoices_user_id');
            $table->index('status', 'idx_invoices_status');
            $table->index('due_date', 'idx_invoices_due_date');
            $table->index('invoice_number', 'idx_invoices_invoice_number');
            $table->index(['tenant_id', 'status'], 'idx_invoices_tenant_status');
            $table->index(['tenant_id', 'user_id'], 'idx_invoices_tenant_user');
            $table->index(['status', 'due_date'], 'idx_invoices_status_due');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_payments_tenant_id');
            $table->index('user_id', 'idx_payments_user_id');
            $table->index('invoice_id', 'idx_payments_invoice_id');
            $table->index('status', 'idx_payments_status');
            $table->index('payment_method', 'idx_payments_method');
            $table->index('paid_at', 'idx_payments_paid_at');
            $table->index(['tenant_id', 'status'], 'idx_payments_tenant_status');
            $table->index(['invoice_id', 'status'], 'idx_payments_invoice_status');
        });

        // Packages table indexes
        Schema::table('packages', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_packages_tenant_id');
            $table->index('status', 'idx_packages_status');
            $table->index(['tenant_id', 'status'], 'idx_packages_tenant_status');
        });

        // Hotspot users table indexes
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->index('username', 'idx_hotspot_users_username');
            $table->index('tenant_id', 'idx_hotspot_users_tenant_id');
            $table->index('status', 'idx_hotspot_users_status');
            $table->index('is_verified', 'idx_hotspot_users_verified');
            $table->index(['tenant_id', 'status'], 'idx_hotspot_users_tenant_status');
            $table->index(['tenant_id', 'is_verified'], 'idx_hotspot_users_tenant_verified');
        });

        // MikroTik routers table indexes
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_mikrotik_routers_tenant_id');
            $table->index('status', 'idx_mikrotik_routers_status');
            $table->index('ip_address', 'idx_mikrotik_routers_ip');
            $table->index(['tenant_id', 'status'], 'idx_mikrotik_routers_tenant_status');
        });

        // Payment gateways table indexes
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_payment_gateways_tenant_id');
            $table->index('is_active', 'idx_payment_gateways_active');
            $table->index(['tenant_id', 'is_active'], 'idx_payment_gateways_tenant_active');
        });

        // Tenants table indexes
        Schema::table('tenants', function (Blueprint $table) {
            $table->index('domain', 'idx_tenants_domain');
            // is_active column does not exist in tenants table - removed
            $table->index('status', 'idx_tenants_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            // username index was not created - removed
            $table->dropIndex('idx_users_tenant_id');
            $table->dropIndex('idx_users_tenant_active');
        });

        // Network users table indexes
        Schema::table('network_users', function (Blueprint $table) {
            $table->dropIndex('idx_network_users_username');
            $table->dropIndex('idx_network_users_tenant_id');
            $table->dropIndex('idx_network_users_status');
            $table->dropIndex('idx_network_users_service_type');
            $table->dropIndex('idx_network_users_tenant_status');
            $table->dropIndex('idx_network_users_tenant_service');
        });

        // Invoices table indexes
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_tenant_id');
            $table->dropIndex('idx_invoices_user_id');
            $table->dropIndex('idx_invoices_status');
            $table->dropIndex('idx_invoices_due_date');
            $table->dropIndex('idx_invoices_invoice_number');
            $table->dropIndex('idx_invoices_tenant_status');
            $table->dropIndex('idx_invoices_tenant_user');
            $table->dropIndex('idx_invoices_status_due');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_tenant_id');
            $table->dropIndex('idx_payments_user_id');
            $table->dropIndex('idx_payments_invoice_id');
            $table->dropIndex('idx_payments_status');
            $table->dropIndex('idx_payments_method');
            $table->dropIndex('idx_payments_paid_at');
            $table->dropIndex('idx_payments_tenant_status');
            $table->dropIndex('idx_payments_invoice_status');
        });

        // Packages table indexes
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('idx_packages_tenant_id');
            $table->dropIndex('idx_packages_status');
            $table->dropIndex('idx_packages_tenant_status');
        });

        // Hotspot users table indexes
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropIndex('idx_hotspot_users_username');
            $table->dropIndex('idx_hotspot_users_tenant_id');
            $table->dropIndex('idx_hotspot_users_status');
            $table->dropIndex('idx_hotspot_users_verified');
            $table->dropIndex('idx_hotspot_users_tenant_status');
            $table->dropIndex('idx_hotspot_users_tenant_verified');
        });

        // MikroTik routers table indexes
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            $table->dropIndex('idx_mikrotik_routers_tenant_id');
            $table->dropIndex('idx_mikrotik_routers_status');
            $table->dropIndex('idx_mikrotik_routers_ip');
            $table->dropIndex('idx_mikrotik_routers_tenant_status');
        });

        // Payment gateways table indexes
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropIndex('idx_payment_gateways_tenant_id');
            $table->dropIndex('idx_payment_gateways_active');
            $table->dropIndex('idx_payment_gateways_tenant_active');
        });

        // Tenants table indexes
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('idx_tenants_domain');
            // is_active index was not created - removed
            $table->dropIndex('idx_tenants_status');
        });
    }
};
