@extends('panels.layouts.app')

@section('title', 'Fixed Billing Configuration')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1 text-foreground">Fixed Billing Configuration</h1>
            <p class="text-muted-foreground mb-0">Configure fixed monthly billing for ISPs</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Fixed Monthly Bill Settings</h5>
                </div>
                <div class="card-body">
                    {{-- Note: Form action currently set to '#'. A proper route and controller method 
                         should be implemented to handle the billing configuration POST request. --}}
                    <form action="#" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="monthly_fee" class="form-label">Monthly Fixed Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="monthly_fee" name="monthly_fee" 
                                       step="0.01" placeholder="0.00">
                            </div>
                            <small class="form-text text-muted-foreground">Fixed monthly fee for the ISP subscription</small>
                        </div>

                        <div class="mb-3">
                            <label for="setup_fee" class="form-label">Setup Fee (One-time)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="setup_fee" name="setup_fee" 
                                       step="0.01" placeholder="0.00">
                            </div>
                            <small class="form-text text-muted-foreground">One-time setup fee (optional)</small>
                        </div>

                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="BDT">BDT - Bangladeshi Taka</option>
                                <option value="INR">INR - Indian Rupee</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="billing_cycle" class="form-label">Billing Cycle</label>
                            <select class="form-select" id="billing_cycle" name="billing_cycle">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_renew" name="auto_renew">
                                <label class="form-check-label" for="auto_renew">
                                    Enable auto-renewal
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-filled ki-check"></i> Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Fixed Billing</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted-foreground mb-3">
                        Fixed billing charges a predetermined amount regardless of usage or number of users.
                    </p>
                    <h6 class="mb-2">Best for:</h6>
                    <ul class="mb-0">
                        <li>Simple subscription model</li>
                        <li>Predictable revenue</li>
                        <li>Easy to manage</li>
                        <li>Flat-rate ISPs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
