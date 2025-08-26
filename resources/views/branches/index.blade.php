@extends('layouts.app')
@section('title', 'Coaches')
@section('content')
    <div class="px-4 pt-4 container-fluid" style="min-height: 82.5vh">
        <div class="row g-4">
            <div class="col-sm-12 col-xl-12">
                <div class="p-4 text-center rounded bg-light">
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <h4>Branches</h4>
                        <a href="{{ route('branches.create') }}" class="btn btn-primary">Add New</a>
                    </div>
                    <div class="table-responsive">
                        <table id="competitions-table" class="datatable table table-striped table-bordered"
                            style="table-layout: auto;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Image</th>
                                    <th>Bio</th>
                                    <th>Address</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>Status</th>
                                    <th>Percentage</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($branches as $i => $branch)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->email }}</td>
                                        <td>{{ $branch->phone }}</td>
                                        <td>
                                            @if ($branch->image)
                                                <img src="{{ asset('storage/' . $branch->image) }}" width="60">
                                            @endif
                                        </td>
                                        <td>{{ $branch->bio }}</td>
                                        <td>{{ $branch->address }}</td>
                                        <td>{{ $branch->country?->name }}</td>
                                        <td>{{ $branch->city?->name }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $branch->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($branch->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $branch->percentage ? $branch->percentage . '%' : '-' }}</td>

                                        <td>
                                            <a href="{{ route('branches.edit', $branch->id) }}" class="me-2"
                                                title="Edit">
                                                <i class="fa fa-edit text-primary"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal{{ $branch->id }}" title="Delete">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="deleteModal{{ $branch->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="deleteModalLabel{{ $branch->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $branch->id }}">
                                                        Delete Confirmation
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this branch?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('branches.destroy', $branch->id) }}"
                                                        method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="competition_id"
                                                            value="{{ $branch->id }}">
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
