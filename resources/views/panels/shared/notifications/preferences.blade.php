@extends('panels.layouts.app')

@section('title', 'Notification Preferences')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Notification Preferences</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('notifications.preferences.update') }}">
                        @csrf
                        
                        <h5 class="mb-3">Email Notifications</h5>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_invoice_generated" id="emailInvoice" checked>
                                <label class="form-check-label" for="emailInvoice">Invoice Generated</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_payment_received" id="emailPayment" checked>
                                <label class="form-check-label" for="emailPayment">Payment Received</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_invoice_overdue" id="emailOverdue" checked>
                                <label class="form-check-label" for="emailOverdue">Invoice Overdue</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_subscription_renewal" id="emailRenewal" checked>
                                <label class="form-check-label" for="emailRenewal">Subscription Renewal Reminder</label>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">SMS Notifications</h5>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sms_invoice_generated" id="smsInvoice" checked>
                                <label class="form-check-label" for="smsInvoice">Invoice Generated</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sms_payment_received" id="smsPayment" checked>
                                <label class="form-check-label" for="smsPayment">Payment Received</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sms_invoice_overdue" id="smsOverdue" checked>
                                <label class="form-check-label" for="smsOverdue">Invoice Overdue</label>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">In-App Notifications</h5>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="inapp_all" id="inappAll" checked>
                                <label class="form-check-label" for="inappAll">All In-App Notifications</label>
                            </div>
                            <small class="text-muted">Disable to stop all in-app notification popups</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
