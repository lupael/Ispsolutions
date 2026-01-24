{{-- Suspension Forecast Widget --}}
<div class="card border-warning" id="suspension-forecast-widget">
    <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
            Today's Suspension Forecast
        </h5>
        <button class="btn btn-sm btn-light" onclick="refreshWidget('suspension_forecast')" title="Refresh">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
    <div class="card-body">
        @if(isset($widgets['suspension_forecast']))
            @php $data = $widgets['suspension_forecast']; @endphp
            
            <div class="row mb-3">
                <div class="col-6">
                    <div class="text-center">
                        <div class="fs-2 fw-bold text-warning">{{ $data['total_count'] }}</div>
                        <small class="text-muted">Customers at Risk</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center">
                        <div class="fs-2 fw-bold text-danger">{{ number_format($data['total_amount'], 2) }}</div>
                        <small class="text-muted">Total Risk Amount (BDT)</small>
                    </div>
                </div>
            </div>

            @if(!empty($data['by_package']))
                <div class="mb-3">
                    <h6 class="text-muted mb-2">By Package</h6>
                    <div class="list-group list-group-flush">
                        @foreach($data['by_package'] as $package)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>{{ $package['package_name'] }}</span>
                                <div>
                                    <span class="badge bg-warning rounded-pill me-2">{{ $package['count'] }}</span>
                                    <small class="text-muted">{{ number_format($package['amount'], 2) }} BDT</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($data['by_zone']))
                <div>
                    <h6 class="text-muted mb-2">By Zone</h6>
                    <div class="list-group list-group-flush">
                        @foreach($data['by_zone'] as $zone)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Zone {{ $zone['zone_id'] }}</span>
                                <div>
                                    <span class="badge bg-secondary rounded-pill me-2">{{ $zone['count'] }}</span>
                                    <small class="text-muted">{{ number_format($zone['amount'], 2) }} BDT</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Data for: {{ $data['date'] }}
                </small>
            </div>
        @else
            <div class="text-center text-muted">
                <p>No suspension forecast data available</p>
            </div>
        @endif
    </div>
</div>
