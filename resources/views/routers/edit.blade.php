@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Router: {{ $router->shortname }}</h1>
    <form action="{{ route('panel.admin.routers.update', $router) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="nasname">NAS Name (IP Address)</label>
            <input type="text" name="nasname" id="nasname" class="form-control" value="{{ $router->nasname }}" required>
        </div>
        <div class="form-group">
            <label for="shortname">Short Name</label>
            <input type="text" name="shortname" id="shortname" class="form-control" value="{{ $router->shortname }}" required>
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <input type="text" name="type" id="type" class="form-control" value="{{ $router->type }}" required>
        </div>
        <div class="form-group">
            <label for="secret">Secret (leave blank to keep unchanged)</label>
            <input type="password" name="secret" id="secret" class="form-control" minlength="16">
        </div>
        <div class="form-group">
            <label for="api_username">API Username</label>
            <input type="text" name="api_username" id="api_username" class="form-control" value="{{ $router->api_username }}" required>
        </div>
        <div class="form-group">
            <label for="api_password">API Password</label>
            <input type="password" name="api_password" id="api_password" class="form-control" value="{{ $router->api_password }}" required>
        </div>
        <div class="form-group">
            <label for="api_port">API Port</label>
            <input type="number" name="api_port" id="api_port" class="form-control" value="{{ $router->api_port }}" required>
        </div>
        <div class="form-group">
            <label for="community">Community</label>
            <input type="text" name="community" id="community" class="form-control" value="{{ $router->community }}">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ $router->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Router</button>
    </form>
</div>
@endsection
