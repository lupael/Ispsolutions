@extends('panels.layouts.app')

@section('title', 'Payment Gateway Settings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-foreground">Payment Gateway Settings</h1>
                    <p class="text-muted-foreground mb-0">Configure payment gateways for accepting online payments</p>
                </div>
                <a href="{{ route('panel.super-admin.payment-gateway.index') }}" class="btn btn-outline-secondary">
                    <i class="ki-filled ki-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ki-filled ki-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ki-filled ki-information-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- bKash Configuration -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="ki-filled ki-wallet"></i> bKash (Bangladesh)
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="bkash-form">
                        @csrf
                        <input type="hidden" name="slug" value="bkash">
                        <input type="hidden" name="name" value="bKash">

                        <div class="mb-3">
                            <label class="form-label">App Key <span class="text-danger">*</span></label>
                            <input type="text" name="configuration[app_key]" class="form-control" 
                                   value="{{ old('configuration.app_key', $gateways['bkash']->configuration['app_key'] ?? '') }}" required>
                            <small class="text-muted">Your bKash merchant App Key</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">App Secret <span class="text-danger">*</span></label>
                            <input type="password" name="configuration[app_secret]" class="form-control" 
                                   value="{{ old('configuration.app_secret', $gateways['bkash']->configuration['app_secret'] ?? '') }}" required>
                            <small class="text-muted">Your bKash merchant App Secret</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="configuration[username]" class="form-control" 
                                   value="{{ old('configuration.username', $gateways['bkash']->configuration['username'] ?? '') }}" required>
                            <small class="text-muted">Your bKash merchant username</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="configuration[password]" class="form-control" 
                                   value="{{ old('configuration.password', $gateways['bkash']->configuration['password'] ?? '') }}" required>
                            <small class="text-muted">Your bKash merchant password</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Secret</label>
                            <input type="text" name="configuration[webhook_secret]" class="form-control" 
                                   value="{{ old('configuration.webhook_secret', $gateways['bkash']->configuration['webhook_secret'] ?? '') }}">
                            <small class="text-muted">Optional: For webhook signature verification</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['bkash']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Test Mode (Sandbox)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['bkash']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Gateway</label>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Webhook URL:</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'bkash']) }}</code>
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ki-filled ki-check"></i> Save bKash Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Nagad Configuration -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="ki-filled ki-wallet"></i> Nagad (Bangladesh)
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="nagad-form">
                        @csrf
                        <input type="hidden" name="slug" value="nagad">
                        <input type="hidden" name="name" value="Nagad">

                        <div class="mb-3">
                            <label class="form-label">Merchant ID <span class="text-danger">*</span></label>
                            <input type="text" name="configuration[merchant_id]" class="form-control" 
                                   value="{{ old('configuration.merchant_id', $gateways['nagad']->configuration['merchant_id'] ?? '') }}" required>
                            <small class="text-muted">Your Nagad merchant ID</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Merchant Number <span class="text-danger">*</span></label>
                            <input type="text" name="configuration[merchant_number]" class="form-control" 
                                   value="{{ old('configuration.merchant_number', $gateways['nagad']->configuration['merchant_number'] ?? '') }}" required>
                            <small class="text-muted">Your Nagad merchant mobile number</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Merchant Private Key <span class="text-danger">*</span></label>
                            <textarea name="configuration[merchant_private_key]" class="form-control font-monospace" 
                                      rows="4" required>{{ old('configuration.merchant_private_key', $gateways['nagad']->configuration['merchant_private_key'] ?? '') }}</textarea>
                            <small class="text-muted">Your private key (PEM format)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nagad Public Key <span class="text-danger">*</span></label>
                            <textarea name="configuration[nagad_public_key]" class="form-control font-monospace" 
                                      rows="4" required>{{ old('configuration.nagad_public_key', $gateways['nagad']->configuration['nagad_public_key'] ?? '') }}</textarea>
                            <small class="text-muted">Nagad's public key (PEM format)</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['nagad']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Test Mode (Sandbox)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['nagad']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Gateway</label>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Webhook URL:</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'nagad']) }}</code>
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="ki-filled ki-check"></i> Save Nagad Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- SSLCommerz Configuration -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="ki-filled ki-shield-tick"></i> SSLCommerz (Bangladesh)
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="sslcommerz-form">
                        @csrf
                        <input type="hidden" name="slug" value="sslcommerz">
                        <input type="hidden" name="name" value="SSLCommerz">

                        <div class="mb-3">
                            <label class="form-label">Store ID <span class="text-danger">*</span></label>
                            <input type="text" name="configuration[store_id]" class="form-control" 
                                   value="{{ old('configuration.store_id', $gateways['sslcommerz']->configuration['store_id'] ?? '') }}" required>
                            <small class="text-muted">Your SSLCommerz store ID</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Store Password <span class="text-danger">*</span></label>
                            <input type="password" name="configuration[store_password]" class="form-control" 
                                   value="{{ old('configuration.store_password', $gateways['sslcommerz']->configuration['store_password'] ?? '') }}" required>
                            <small class="text-muted">Your SSLCommerz store password</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select name="configuration[currency]" class="form-select">
                                <option value="BDT" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (Bangladeshi Taka)</option>
                                <option value="USD" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                <option value="EUR" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                <option value="GBP" {{ old('configuration.currency', $gateways['sslcommerz']->configuration['currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP (British Pound)</option>
                            </select>
                            <small class="text-muted">Default currency for transactions</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['sslcommerz']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Test Mode (Sandbox)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['sslcommerz']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Gateway</label>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Webhook URL (IPN):</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'sslcommerz']) }}</code>
                            </small>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="ki-filled ki-check"></i> Save SSLCommerz Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stripe Configuration -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="ki-filled ki-credit-card"></i> Stripe (International)
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('panel.super-admin.payment-gateway.store') }}" method="POST" id="stripe-form">
                        @csrf
                        <input type="hidden" name="slug" value="stripe">
                        <input type="hidden" name="name" value="Stripe">

                        <div class="mb-3">
                            <label class="form-label">Live Publishable Key <span class="text-danger">*</span></label>
                            <input type="text" name="configuration[publishable_key]" class="form-control" 
                                   value="{{ old('configuration.publishable_key', $gateways['stripe']->configuration['publishable_key'] ?? '') }}" required>
                            <small class="text-muted">Starts with pk_live_</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Live Secret Key <span class="text-danger">*</span></label>
                            <input type="password" name="configuration[secret_key]" class="form-control" 
                                   value="{{ old('configuration.secret_key', $gateways['stripe']->configuration['secret_key'] ?? '') }}" required>
                            <small class="text-muted">Starts with sk_live_</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test Publishable Key</label>
                            <input type="text" name="configuration[test_publishable_key]" class="form-control" 
                                   value="{{ old('configuration.test_publishable_key', $gateways['stripe']->configuration['test_publishable_key'] ?? '') }}">
                            <small class="text-muted">Starts with pk_test_</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test Secret Key</label>
                            <input type="password" name="configuration[test_secret_key]" class="form-control" 
                                   value="{{ old('configuration.test_secret_key', $gateways['stripe']->configuration['test_secret_key'] ?? '') }}">
                            <small class="text-muted">Starts with sk_test_</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Secret <span class="text-danger">*</span></label>
                            <input type="password" name="configuration[webhook_secret]" class="form-control" 
                                   value="{{ old('configuration.webhook_secret', $gateways['stripe']->configuration['webhook_secret'] ?? '') }}" required>
                            <small class="text-muted">Starts with whsec_</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select name="configuration[currency]" class="form-select">
                                <option value="usd" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? 'usd') === 'usd' ? 'selected' : '' }}>USD (US Dollar)</option>
                                <option value="eur" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? '') === 'eur' ? 'selected' : '' }}>EUR (Euro)</option>
                                <option value="gbp" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? '') === 'gbp' ? 'selected' : '' }}>GBP (British Pound)</option>
                                <option value="bdt" {{ old('configuration.currency', $gateways['stripe']->configuration['currency'] ?? '') === 'bdt' ? 'selected' : '' }}>BDT (Bangladeshi Taka)</option>
                            </select>
                            <small class="text-muted">Default currency for transactions</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="test_mode" value="1" 
                                   {{ old('test_mode', $gateways['stripe']->test_mode ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Test Mode</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $gateways['stripe']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Gateway</label>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Webhook URL:</strong><br>
                                <code>{{ route('webhooks.payment', ['gateway' => 'stripe']) }}</code><br>
                                <strong>Events to listen:</strong> payment_intent.succeeded, checkout.session.completed
                            </small>
                        </div>

                        <button type="submit" class="btn btn-info w-100">
                            <i class="ki-filled ki-check"></i> Save Stripe Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Documentation Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ki-filled ki-book"></i> Setup Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="setupAccordion">
                        <!-- bKash Setup -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bkashSetup">
                                    bKash Setup Guide
                                </button>
                            </h2>
                            <div id="bkashSetup" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Register for a bKash merchant account at <a href="https://www.bkash.com/merchants" target="_blank">bKash Merchant Portal</a></li>
                                        <li>Get your credentials: App Key, App Secret, Username, and Password</li>
                                        <li>Configure the webhook URL in your bKash merchant dashboard</li>
                                        <li>Enable test mode for sandbox testing</li>
                                        <li>Test with sandbox credentials before going live</li>
                                    </ol>
                                    <p><strong>API Documentation:</strong> <a href="https://developer.bkash.com/" target="_blank">https://developer.bkash.com/</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- Nagad Setup -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#nagadSetup">
                                    Nagad Setup Guide
                                </button>
                            </h2>
                            <div id="nagadSetup" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Register for a Nagad merchant account at <a href="https://nagad.com.bd/" target="_blank">Nagad Merchant Portal</a></li>
                                        <li>Generate RSA key pairs or get them from Nagad</li>
                                        <li>Get Merchant ID, Merchant Number, and exchange public keys</li>
                                        <li>Configure the callback URL in your Nagad merchant dashboard</li>
                                        <li>Test with sandbox credentials before going live</li>
                                    </ol>
                                    <p><strong>API Documentation:</strong> <a href="https://developer.nagad.com.bd/" target="_blank">https://developer.nagad.com.bd/</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- SSLCommerz Setup -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sslcommerzSetup">
                                    SSLCommerz Setup Guide
                                </button>
                            </h2>
                            <div id="sslcommerzSetup" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Register for an SSLCommerz merchant account at <a href="https://sslcommerz.com/" target="_blank">SSLCommerz Portal</a></li>
                                        <li>Get your Store ID and Store Password</li>
                                        <li>Configure the IPN (webhook) URL in your SSLCommerz dashboard</li>
                                        <li>Enable test mode for sandbox testing</li>
                                        <li>Test with sandbox credentials before going live</li>
                                    </ol>
                                    <p><strong>API Documentation:</strong> <a href="https://developer.sslcommerz.com/" target="_blank">https://developer.sslcommerz.com/</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Setup -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#stripeSetup">
                                    Stripe Setup Guide
                                </button>
                            </h2>
                            <div id="stripeSetup" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Create a Stripe account at <a href="https://stripe.com/" target="_blank">stripe.com</a></li>
                                        <li>Get your API keys from the Stripe Dashboard (Developers → API keys)</li>
                                        <li>Create a webhook endpoint in Stripe Dashboard (Developers → Webhooks)</li>
                                        <li>Select events: <code>payment_intent.succeeded</code> and <code>checkout.session.completed</code></li>
                                        <li>Get the webhook signing secret (starts with whsec_)</li>
                                        <li>Test with test keys before using live keys</li>
                                    </ol>
                                    <p><strong>API Documentation:</strong> <a href="https://stripe.com/docs/api" target="_blank">https://stripe.com/docs/api</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
