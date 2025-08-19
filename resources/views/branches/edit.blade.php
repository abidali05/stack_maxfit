@extends('layouts.app')
@section('title', 'Edit branch')
@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded p-4">
                    <h4>Edit Branch</h4>
                    <form action="{{ route('branches.update', $branch->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $branch->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $branch->email }}" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $branch->phone }}">
                        </div>
                        <div class="mb-3">
                            <label>Image</label>
                            <input type="file" name="image" class="form-control">
                            @if ($branch->image)
                                <img src="{{ asset('storage/' . $branch->image) }}" width="80" class="mt-2">
                            @endif
                        </div>
                        <div class="mb-3">
                            <label>Bio</label>
                            <textarea name="bio" class="form-control">{{ $branch->bio }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ $branch->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $branch->status === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                    <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to delete this coach?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
