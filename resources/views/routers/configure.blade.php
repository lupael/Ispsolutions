@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Configure Router: {{ $router->shortname }}</h1>
    <form action="{{ route('panel.admin.routers.configure.store', $router) }}" method="POST">
        @csrf
        <p>Click the button below to apply the standard configuration to the router.</p>
        <button type="submit" class="btn btn-primary">Configure Router</button>
    </form>
</div>
@endsection
