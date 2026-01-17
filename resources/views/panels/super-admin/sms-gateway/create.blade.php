@extends('layouts.app')

@section('title', 'Add SMS Gateway')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-foreground">Add SMS Gateway</h1>
                    <p class="text-muted-foreground mb-0">Configure a new SMS gateway</p>
                </div>
                <a href="{{ route('panel.super-admin.sms-gateway.index') }}" class="btn btn-outline-secondary">
                    <i class="ki-filled ki-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('panel.super-admin.sms-gateway.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Gateway Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="provider" class="form-label">Provider <span class="text-danger">*</span></label>
                            <select class="form-select @error('provider') is-invalid @enderror" 
                                    id="provider" name="provider" required>
                                <option value="">Select Provider</option>
                                <option value="twilio">Twilio</option>
                                <option value="nexmo">Nexmo (Vonage)</option>
                                <option value="clickatell">Clickatell</option>
                                <option value="bulksms">BulkSMS</option>
                                <option value="sslwireless">SSL Wireless</option>
                                <option value="custom">Custom</option>
                            </select>
                            @error('provider')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('api_key') is-invalid @enderror" 
                                   id="api_key" name="api_key" value="{{ old('api_key') }}" required>
                            @error('api_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_secret" class="form-label">API Secret</label>
                            <input type="password" class="form-control @error('api_secret') is-invalid @enderror" 
                                   id="api_secret" name="api_secret" value="{{ old('api_secret') }}">
                            @error('api_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">Optional for some providers</small>
                        </div>

                        <div class="mb-3">
                            <label for="sender_id" class="form-label">Sender ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('sender_id') is-invalid @enderror" 
                                   id="sender_id" name="sender_id" value="{{ old('sender_id') }}" 
                                   placeholder="MYISP" maxlength="20" required>
                            @error('sender_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted-foreground">The sender name shown to recipients (max 20 chars)</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('panel.super-admin.sms-gateway.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-filled ki-check"></i> Add Gateway
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">SMS Gateway Information</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted-foreground mb-3">
                        Configure SMS gateway to send notifications, alerts, and marketing messages to customers.
                    </p>
                    <h6 class="mb-2">Common Uses:</h6>
                    <ul class="mb-3">
                        <li>Payment reminders</li>
                        <li>Service notifications</li>
                        <li>Due date alerts</li>
                        <li>Promotional messages</li>
                    </ul>
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="ki-filled ki-information"></i>
                            Check with your provider for rate limits and message costs.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
