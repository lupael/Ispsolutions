{{-- SMS Usage Widget --}}
<div class="card border-info" id="sms-usage-widget">
    <div class="card-header bg-info bg-opacity-10 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-sms text-info me-2"></i>
            SMS Usage Today
        </h5>
        <button class="btn btn-sm btn-light" onclick="refreshWidget('sms_usage')" title="Refresh">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
    <div class="card-body">
        @if(isset($widgets['sms_usage']))
            @php $data = $widgets['sms_usage']; @endphp
            
            <div class="text-center mb-3">
                <div class="fs-1 fw-bold text-info">{{ $data['total_sent'] }}</div>
                <small class="text-muted">Total SMS Sent</small>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4 text-center">
                    <div class="card bg-success bg-opacity-10">
                        <div class="card-body py-2">
                            <div class="fs-5 fw-bold text-success">{{ $data['sent_count'] }}</div>
                            <small class="text-muted">Delivered</small>
                        </div>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="card bg-danger bg-opacity-10">
                        <div class="card-body py-2">
                            <div class="fs-5 fw-bold text-danger">{{ $data['failed_count'] }}</div>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="card bg-warning bg-opacity-10">
                        <div class="card-body py-2">
                            <div class="fs-5 fw-bold text-warning">{{ $data['pending_count'] }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Cost Today</span>
                        <span class="fw-bold text-danger">{{ number_format($data['total_cost'], 4) }} BDT</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Used Balance</span>
                        <span class="fw-bold">{{ number_format($data['used_balance'], 4) }} BDT</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Remaining Balance</span>
                        <span class="fw-bold text-success">{{ number_format($data['remaining_balance'], 2) }} BDT</span>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mb-0">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Success Rate: 
                    @php
                        $successRate = $data['total_sent'] > 0 
                            ? round(($data['sent_count'] / $data['total_sent']) * 100, 1) 
                            : 0;
                    @endphp
                    <strong>{{ $successRate }}%</strong>
                </small>
            </div>

            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Data for: {{ $data['date'] }}
                </small>
            </div>
        @else
            <div class="text-center text-muted">
                <p>No SMS usage data available</p>
            </div>
        @endif
    </div>
</div>
