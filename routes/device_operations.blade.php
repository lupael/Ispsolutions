@extends('layouts.app') {{-- Or your admin layout, e.g., 'layouts.admin' --}}

@section('title', 'Device Operations Log')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Device Operations Log</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                @if(empty($logs))
                    <div class="alert alert-info">
                        The log file is empty or does not exist.
                    </div>
                @else
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 180px;">Timestamp</th>
                                <th style="width: 100px;">Level</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log['timestamp'] }}</td>
                                    <td>
                                        @php
                                            $levelClass = [
                                                'error' => 'danger',
                                                'warning' => 'warning',
                                                'info' => 'info',
                                                'debug' => 'secondary',
                                            ][$log['level']] ?? 'light';
                                        @endphp
                                        <span class="badge badge-{{ $levelClass }}">{{ strtoupper($log['level']) }}</span>
                                    </td>
                                    <td style="word-break: break-all;">
                                        <pre style="white-space: pre-wrap; margin: 0; background: transparent; border: none; padding: 0;">{{ $log['message'] }}</pre>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>
@endsection
