@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Super Admins
                        <a href="{{ route('developer.create') }}" class="btn btn-primary float-right">Create Super Admin</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($superAdmins as $superAdmin)
                                    <tr>
                                        <td>{{ $superAdmin->name }}</td>
                                        <td>{{ $superAdmin->email }}</td>
                                        <td>
                                            <a href="{{ route('developer.edit', $superAdmin) }}" class="btn btn-primary">Edit</a>
                                            <form action="{{ route('developer.destroy', $superAdmin) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
