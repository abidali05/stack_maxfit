@extends('layouts.app')
@section('title', 'Competitions')
@section('content')
    <div class="px-4 pt-4 container-fluid" style="min-height: 82.5vh">
        <div class="row g-4">
            <div class="col-sm-12 col-xl-12">
                <div class="p-4 text-center rounded bg-light">
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0">Competition Details</h6>
                    </div>
                    <form action="{{ route('competition-users.update', $competitionUser->id) }}" method="POST" id="competitionForm">
                        @csrf
                        @method('PUT')

                        @foreach ($exercises as $exercise)
                            <div class="mb-3">
                                <label for="score_{{ $exercise->id }}" class="form-label">{{ $exercise->name }}</label>
                                <input type="number" step="0.01" name="scores[{{ $exercise->id }}]" class="form-control score-input"
                                    value="{{ $results[$exercise->id]->score ?? '' }}" required min="0">
                                <div class="text-danger error-message" id="score_{{ $exercise->id }}-error"></div>
                            </div>
                        @endforeach

                        <button type="button" class="btn btn-success" id="submitScores">Save Scores</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
        .error-message {
            font-size: 0.875rem;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Client-side validation
            $('#submitScores').on('click', function(e) {
                e.preventDefault();
                let isValid = true;
                const errorElements = document.getElementsByClassName('error-message');

                // Clear previous errors
                Array.from(errorElements).forEach(element => {
                    element.textContent = '';
                });

                // Validate each score input
                $('.score-input').each(function() {
                    const input = $(this);
                    const exerciseId = input.attr('name').match(/\d+/)[0]; // Extract exercise ID from name
                    const value = parseFloat(input.val()) || 0;

                    if (!input.val().trim()) {
                        document.getElementById(`score_${exerciseId}-error`).textContent = 'Score is required.';
                        isValid = false;
                    } else if (value < 0) {
                        document.getElementById(`score_${exerciseId}-error`).textContent = 'Score cannot be negative.';
                        isValid = false;
                    }
                });

                if (isValid) {
                    $('#competitionForm').submit();
                }
            });
        });
    </script>
@endsection
