@extends('panels.layouts.app')

@section('title', 'Error Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Error Logs (Last 100 entries)</h3>
                    <p class="card-subtitle">Recent error entries from Laravel log file</p>
                </div>
                <div class="card-body">
                    @if($logs->count() > 0)
                        <div class="log-container" style="max-height: 600px; overflow-y: auto;">
                            @foreach($logs as $log)
                                <div class="log-entry mb-2 p-2 border-bottom">
                                    <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">{{ $log }}</pre>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> No errors found in the log file!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
