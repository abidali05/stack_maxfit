@extends('layouts.app')
@section('title', 'Edit Coach')
@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded p-4">
                <h4>Edit Coach</h4>
                <form action="{{ route('coaches.update', $coach->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $coach->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $coach->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $coach->phone }}">
                    </div>
                    <div class="mb-3">
                        <label>Image</label>
                        <input type="file" name="image" class="form-control">
                        @if($coach->image)
                            <img src="{{ asset('storage/' . $coach->image) }}" width="80" class="mt-2">
                        @endif
                    </div>
                    <div class="mb-3">
                        <label>Bio</label>
                        <textarea name="bio" class="form-control">{{ $coach->bio }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('coaches.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
                <form action="{{ route('coaches.destroy', $coach->id) }}" method="POST" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this coach?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
