{{-- Collection Target Widget --}}
<div class="card border-success" id="collection-target-widget">
    <div class="card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-bullseye text-success me-2"></i>
            Collection Target
        </h5>
        <button class="btn btn-sm btn-light" onclick="refreshWidget('collection_target')" title="Refresh">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
    <div class="card-body">
        @if(isset($widgets['collection_target']))
            @php $data = $widgets['collection_target']; @endphp
            
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Target: {{ number_format($data['target_amount'], 2) }} BDT</span>
                        <span class="fw-bold text-success">{{ $data['percentage_collected'] }}%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $data['percentage_collected'] }}%;" 
                             aria-valuenow="{{ $data['percentage_collected'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ number_format($data['collected_amount'], 2) }} BDT
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <div class="fs-5 fw-bold text-success">{{ number_format($data['collected_amount'], 2) }}</div>
                            <small class="text-muted">Collected (BDT)</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card bg-light">
                        <div class="card-body text-center py-2">
                            <div class="fs-5 fw-bold text-warning">{{ number_format($data['pending_amount'], 2) }}</div>
                            <small class="text-muted">Pending (BDT)</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-4 text-center">
                    <div class="fs-4 fw-bold">{{ $data['total_bills'] }}</div>
                    <small class="text-muted">Total Bills</small>
                </div>
                <div class="col-4 text-center border-start border-end">
                    <div class="fs-4 fw-bold text-success">{{ $data['paid_bills'] }}</div>
                    <small class="text-muted">Paid</small>
                </div>
                <div class="col-4 text-center">
                    <div class="fs-4 fw-bold text-warning">{{ $data['pending_bills'] }}</div>
                    <small class="text-muted">Pending</small>
                </div>
            </div>

            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    Bills due: {{ $data['date'] }}
                </small>
            </div>
        @else
            <div class="text-center text-muted">
                <p>No collection target data available</p>
            </div>
        @endif
    </div>
</div>
