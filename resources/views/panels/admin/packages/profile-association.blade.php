@extends('panels.layouts.app')

@section('title', 'PPPoE Profile Association - ' . $package->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">PPPoE Profile Association for {{ $package->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('panel.admin.packages.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Packages
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> Associate this package with PPPoE profiles on different routers. 
                        When a customer is assigned this package, the system can automatically apply the appropriate profile.
                    </div>

                    <form action="{{ route('panel.packages.profiles.update', $package) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="auto_apply" name="auto_apply" 
                                       value="1" checked>
                                <label class="custom-control-label" for="auto_apply">
                                    Automatically apply profile when package is assigned to customer
                                </label>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Router-Profile Mappings</h5>

                        @if($routers->count() === 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No active routers found. Please add routers before configuring profile associations.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="30%">Router</th>
                                            <th width="30%">PPPoE Profile</th>
                                            <th width="20%">Current Mapping</th>
                                            <th width="20%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($routers as $router)
                                        <tr>
                                            <td>
                                                <strong>{{ $router->name }}</strong><br>
                                                <small class="text-muted">{{ $router->host }}:{{ $router->port }}</small>
                                            </td>
                                            <td>
                                                <input type="hidden" name="mappings[{{ $loop->index }}][router_id]" value="{{ $router->id }}">
                                                <select name="mappings[{{ $loop->index }}][profile_name]" 
                                                        class="form-control profile-select" 
                                                        data-router-id="{{ $router->id }}">
                                                    <option value="">-- Select Profile --</option>
                                                    @if(isset($profilesByRouter[$router->id]))
                                                        @foreach($profilesByRouter[$router->id] as $profile)
                                                            <option value="{{ $profile->name }}"
                                                                    {{ isset($mappings[$router->id]) && $mappings[$router->id]->profile_name == $profile->name ? 'selected' : '' }}>
                                                                {{ $profile->name }}
                                                                @if($profile->rate_limit)
                                                                    - {{ $profile->rate_limit }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                @if(isset($mappings[$router->id]))
                                                    <span class="badge badge-info">
                                                        {{ $mappings[$router->id]->profile_name ?? 'N/A' }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Not mapped</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($mappings[$router->id]))
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Mapped
                                                    </span>
                                                @else
                                                    <span class="badge badge-light">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Save Profile Associations
                                    </button>
                                    <a href="{{ route('panel.admin.packages.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle"></i> How Profile Association Works
                    </h3>
                </div>
                <div class="card-body">
                    <ol>
                        <li><strong>Map Profiles:</strong> Associate this package with PPPoE profiles on each router.</li>
                        <li><strong>Auto-Apply:</strong> When enabled, the system automatically applies the profile when assigning this package to a customer.</li>
                        <li><strong>Router-Specific:</strong> Each router can have a different profile for the same package, allowing flexibility across your network.</li>
                        <li><strong>Manual Override:</strong> You can still manually change a customer's profile even after automatic assignment.</li>
                    </ol>
                    <p class="mb-0">
                        <strong>Note:</strong> Make sure profiles exist on your routers before configuring associations. 
                        You can manage profiles in the Router Management section.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any dynamic profile loading or validation here
    const profileSelects = document.querySelectorAll('.profile-select');
    
    profileSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Could add AJAX call here to validate profile or get more details
            console.log('Profile changed for router:', this.dataset.routerId);
        });
    });
});
</script>
@endpush
@endsection
