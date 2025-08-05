@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <div class="container-fluid pt-4 px-4" style="min-height: 82.5vh">
        <div class="row g-4">
            <div class="col-sm-12 col-xl-12">
                <div class="bg-light text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Users</h6>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">Add New User</a>
                    </div>
                    <div class="table-responsive">  
                        <table class="table text-start align-middle table-bordered  mb-0 datatable">
                            <thead>
                                <tr class="text-dark">
                                    <th scope="col">S.No</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Number</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $i=> $user)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <img src="{{ $user->image ?? asset('assets/images/user.jpg') }}" alt="User Image" class="rounded-circle" width="50" height="50">
                                        </td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->number }}</td>
                                        
                                        <td class="text-capitalize">{{ $user->role }}</td>
                                        <td class="d-flex align-items-center justify-content-center vertical-align-middle">

                                                <a href="{{ route('users.edit', $user->id) }}" class="me-2"> <i class="fa fa-edit"></i> </a>
                                                <a href="#" class="" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}"><i class="fa fa-trash text-danger"></i></a>
                                               
                                            
                                        </td>
                                    </tr>

                                    {{-- confirm delete modal  --}}
                                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $user->id }}">Delete Confirmation</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this record?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="id" value="{{ $user->id }}">
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