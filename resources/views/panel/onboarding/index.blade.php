@extends('panels.layouts.app')

@section('title', 'Onboarding Checklist')

@section('content')
<div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <h2>Complete Your Onboarding</h2>
            </div>
            <div class="card-toolbar">
                <div class="badge badge-light-success fs-3">
                    {{ $progress }}% Complete
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="mb-10">
                <div class="progress h-6px mb-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"
                        aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="text-muted">
                    Complete all required steps to unlock full access to the ISP management system.
                </p>
            </div>

            <div class="timeline timeline-bordered">
                @foreach ($steps as $step)
                    <div class="timeline-item">
                        <div class="timeline-line w-40px"></div>

                        <div class="timeline-icon symbol symbol-circle symbol-40px
                            @if ($step['completed']) bg-success
                            @else bg-light @endif">
                            <div class="symbol-label">
                                @if ($step['completed'])
                                    <i class="ki-duotone ki-check fs-2 text-white">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                @else
                                    <span class="fs-6 fw-bold text-gray-600">{{ $step['number'] }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="timeline-content mb-10 mt-n1">
                            <div class="pe-3 mb-5">
                                <div class="fs-5 fw-bold mb-2
                                    @if ($step['completed']) text-success
                                    @else text-gray-800 @endif">
                                    Step {{ $step['number'] }}: {{ $step['name'] }}
                                    @if ($step['required'])
                                        <span class="badge badge-light-danger ms-2">Required</span>
                                    @endif
                                    @if ($step['completed'])
                                        <span class="badge badge-light-success ms-2">Completed</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center mt-1 fs-6">
                                    <div class="text-muted me-2 fs-7">{{ $step['description'] }}</div>
                                </div>
                            </div>

                            @if (!$step['completed'] && isset($step['route']))
                                <div class="overflow-auto pb-5">
                                    <a href="{{ route($step['route']) }}"
                                        class="btn btn-sm btn-primary">
                                        Complete This Step
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($progress === 100)
                <div class="alert alert-success d-flex align-items-center p-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Congratulations!</h4>
                        <span>You have completed all onboarding steps and can now access the full system.</span>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="{{ route('panel.admin.dashboard') }}" class="btn btn-lg btn-success">
                        Go to Dashboard
                        <i class="ki-duotone ki-arrow-right fs-3 ms-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
