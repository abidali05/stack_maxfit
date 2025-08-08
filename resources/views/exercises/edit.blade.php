@extends('layouts.app')
@section('title', 'Edit Exercise')
@section('content')
    <div class="container-fluid pt-4 px-4" style="min-height: 82.5vh">
        <form action="{{ route('exercises.update', $exercise->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="bg-light rounded p-4">

                        {{-- Flash messages --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" class="form-control" placeholder="Name"
                                    value="{{ old('name', $exercise->name) }}" name="name" required>
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>


                            <div class="col-md-6">
                                <label for="exercise_category_id" class="form-label">Exercise Category</label>
                                <select name="exercise_category_id" id="exercise_category_id" class="form-select" required>
                                    <option value="" disabled>Select Category</option>
                                    @foreach ($exercises as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('exercise_category_id', $exercise->exercise_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('exercise_category_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Genz</label>
                                <select name="genz" class="form-select" required>
                                    <option value="" disabled {{ old('genz', $exercise->genz) ? '' : 'selected' }}>
                                        Select Genz</option>
                                    <option value="both" {{ old('genz', $exercise->genz) == 'both' ? 'selected' : '' }}>
                                        Both
                                    </option>
                                    <option value="motherfits"
                                        {{ old('genz', $exercise->genz) == 'motherfits' ? 'selected' : '' }}>Mother Fit
                                    </option>
                                    <option value="fatherfits"
                                        {{ old('genz', $exercise->genz) == 'fatherfits' ? 'selected' : '' }}>Father Fit
                                    </option>
                                </select>
                                @error('genz')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Optional description...">{{ old('description', $exercise->description) }}</textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" name="image" id="image" accept="image/*">
                                @if ($exercise->image)
                                    <img src="{{ asset('storage/' . $exercise->image) }}" class="img-thumbnail mt-2"
                                        width="150" alt="Current Image">
                                @endif
                                @error('image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="video_file" class="form-label">Video File</label>
                                <input type="text" class="form-control" name="video_file" value="{{ $exercise->video_file }}" id="video_file">

                                @error('video_file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Update changes</button>
                            <a href="{{ route('exercises.index') }}" class="btn btn-outline-danger">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
