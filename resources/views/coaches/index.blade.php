@extends('layouts.app')
@section('title', 'Coaches')
@section('content')
    <div class="px-4 pt-4 container-fluid" style="min-height: 82.5vh">
        <div class="row g-4">
            <div class="col-sm-12 col-xl-12">
                <div class="p-4 text-center rounded bg-light">
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <h4>Coaches</h4>
                        <a href="{{ route('coaches.create') }}" class="btn btn-primary">Add New</a>
                    </div>
                    <div class="table-responsive">
                         <table id="competitions-table" class="table mb-0 align-middle text-start table-bordered datatable"
                            style="table-layout: auto;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Image</th>
                                <th>Bio</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coaches as $i => $coach)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $coach->name }}</td>
                                    <td>{{ $coach->email }}</td>
                                    <td>{{ $coach->phone }}</td>
                                    <td>{{ $coach->address }}</td>
                                    <td>{{ $coach->country->name ?? '-' }}</td>
                                    <td>{{ $coach->city->name ?? '-' }}</td>
                                    <td>
                                        @if ($coach->image)
                                            <img src="{{ asset('storage/' . $coach->image) }}" width="60">
                                        @endif
                                    </td>
                                    <td>{{ $coach->bio }}</td>

                                    <td>
                                        <a href="{{ route('coaches.edit', $coach->id) }}" class="me-2" title="Edit">
                                            <i class="fa fa-edit text-primary"></i>
                                        </a>
                                        <a href="#" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $coach->id }}" title="Delete">
                                            <i class="fa fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="deleteModal{{ $coach->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="deleteModalLabel{{ $coach->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $coach->id }}">
                                                    Delete Confirmation
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this coach?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('coaches.destroy', $coach->id) }}" method="POST"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="competition_id"
                                                        value="{{ $coach->id }}">
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

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        #competitions-table th,
        #competitions-table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
    </style>
@endpush

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#competitions-table').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [],
                language: {
                    search: "",
                    searchPlaceholder: "Search competitions..."
                }
            });
        });
    </script>
@endpush
