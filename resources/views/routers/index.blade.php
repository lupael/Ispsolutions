@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Routers</h1>
    <a href="{{ route('panel.admin.routers.create') }}" class="btn btn-primary">Add New Router</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Short Name</th>
                <th>NAS Name (IP)</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($routers as $router)
            <tr>
                <td>{{ $router->id }}</td>
                <td>{{ $router->shortname }}</td>
                <td>{{ $router->nasname }}</td>
                <td>{{ $router->type }}</td>
                <td>
                    <a href="{{ route('panel.admin.routers.edit', $router) }}" class="btn btn-sm btn-warning">Edit</a>
                    <a href="{{ route('panel.admin.routers.configure', $router) }}" class="btn btn-sm btn-info">Configure</a>
                    <form action="{{ route('panel.admin.routers.destroy', $router) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection