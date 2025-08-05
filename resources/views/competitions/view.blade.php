@extends('layouts.app')
@section('title', 'Competition Details')
@section('content')
    <div class="px-4 pt-4 container-fluid" style="min-height: 82.5vh">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="p-4 rounded bg-light">
                    <h4 class="mb-3">Competition: {{ $competition->name ?? '-' }}</h4>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Coach:</strong> {{ $competition->coach_name ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Location:</strong> {{ $competition->city ?? '-' }}, {{ $competition->country ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Dates:</strong> {{ $competition->start_date ?? '-' }} to {{ $competition->end_date ?? '-' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Time:</strong> {{ $competition->start_time ?? '-' }} - {{ $competition->end_time ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Age Group:</strong> {{ $competition->age_group ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Status:</strong> {{ $competition->status ?? '-' }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong> {{ $competition->description ?? '-' }}
                    </div>
                    <hr>
                    <h5>Participants & Results</h5>
                    <form action="{{ route('results.update', $competition->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Name</th>
                                        <th>Percentage</th>
                                        <th>PushUps Per Min</th>
                                        <th>Position</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($competition->competitionUsers ?? [] as $i => $cu)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $cu->user->name ?? '-' }}</td>
                                            <td>
                                                <input type="hidden" name="results[{{ $i }}][competition_user_id]" value="{{ $cu->id }}">
                                                <input type="text" name="results[{{ $i }}][percentage]" class="form-control" value="{{ $cu->competitionResult->percentage ?? '' }}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="results[{{ $i }}][per_min]" class="form-control" value="{{ $cu->competitionResult->per_min ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="results[{{ $i }}][position]" class="form-control" value="{{ $cu->competitionResult->position ?? '' }}" readonly>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Update Results</button>
                        <a href="{{ route('results.index') }}" class="btn btn-outline-danger mt-3">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
