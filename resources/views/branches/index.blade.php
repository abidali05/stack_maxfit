@extends('layouts.app')
@section('title', 'Branches')
@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded p-4">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Branches</h4>
                    <a href="{{ route('branches.create') }}" class="btn btn-primary">Add New</a>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Image</th>
                            <th>Bio</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $i => $branch)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->email }}</td>
                            <td>{{ $branch->phone }}</td>
                            <td>@if($branch->image)<img src="{{ asset('storage/' . $branch->image) }}" width="60">@endif</td>
                            <td>{{ $branch->bio }}</td>
                            <td>
                                <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" style="display:inline-block;">
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
        </div>
    </div>
</div>
@endsection
