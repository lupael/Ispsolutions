@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Router</h1>
    <form action="{{ route('panel.admin.routers.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nasname">NAS Name (IP Address)</label>
            <input type="text" name="nasname" id="nasname" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="shortname">Short Name</label>
            <input type="text" name="shortname" id="shortname" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <input type="text" name="type" id="type" class="form-control" value="mikrotik" required>
        </div>
        <div class="form-group">
            <label for="secret">Secret</label>
            <input type="password" name="secret" id="secret" class="form-control" required minlength="16">
        </div>
        <div class="form-group">
            <label for="api_username">API Username</label>
            <input type="text" name="api_username" id="api_username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="api_password">API Password</label>
            <input type="password" name="api_password" id="api_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="api_port">API Port</label>
            <input type="number" name="api_port" id="api_port" class="form-control" value="8728" required>
        </div>
        <div class="form-group">
            <label for="community">Community</label>
            <input type="text" name="community" id="community" class="form-control" value="billing">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Router</button>
    </form>
</div>
@endsection
