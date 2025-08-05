@extends('layouts.app')
@section('title', 'Create Competitions')
@section('content')
    <div class="px-4 pt-4 container-fluid" style="min-height: 82.5vh">
        <form action="{{ route('competitions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="p-4 rounded bg-light">

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
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Age Group</label>
                                <input type="number" class="form-control" name="age_group" id="age_group" required>
                                @error('age_group')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Coach Name</label>
                                <input type="text" class="form-control" name="coach_name" required>
                                @error('coach_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" required>
                                @error('country')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required>
                                @error('city')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                                @error('start_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                                @error('end_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control" name="start_time" required>
                                @error('start_time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" name="end_time" required>
                                @error('end_time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Time Allowed (minutes)</label>
                                <input type="number" class="form-control" name="time_allowed" min="1" required>
                                @error('time_allowed')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Select Genz</label>
                                <select class="form-control" id="genz" disabled required>
                                    <option value="">Select</option>
                                    <option value="motherfits">Motherfits</option>
                                    <option value="fatherfits">Fatherfits</option>
                                </select>
                                <input type="hidden" name="genz" id="genz_hidden">
                                @error('genz')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Organization Type</label>
                                <select class="form-control" name="org_type" id="org_type" required>
                                    <option value="">Select</option>
                                    @foreach ($organizationTypes as $organizationType)
                                        <option value="{{ $organizationType->id }}">{{ $organizationType->name }}</option>
                                    @endforeach
                                </select>
                                @error('org_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Organization</label>
                                <select class="form-control" name="org" id="org" required>
                                    <option value="">Select</option>
                                </select>
                                @error('org')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                @error('image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"></textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Save Competition</button>
                            <a href="{{ route('competitions.index') }}" class="btn btn-outline-danger">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Function to update Genz value based on age group
        function updateGenzValue(ageGroup) {
            const genzSelect = document.getElementById('genz');
            const genzHidden = document.getElementById('genz_hidden');

            if (ageGroup < 14) {
                genzSelect.value = 'motherfits';
                genzHidden.value = 'motherfits';
            } else {
                genzSelect.value = 'fatherfits';
                genzHidden.value = 'fatherfits';
            }
        }

        // Set initial Genz value on page load if age_group has a value
        window.addEventListener('load', function() {
            const ageGroupInput = document.getElementById('age_group');
            const ageGroup = parseInt(ageGroupInput.value);
            if (!isNaN(ageGroup)) {
                updateGenzValue(ageGroup);
            }
        });

        // Update Genz value on age_group input change
        document.getElementById('age_group').addEventListener('input', function() {
            const ageGroup = parseInt(this.value);
            if (!isNaN(ageGroup)) {
                updateGenzValue(ageGroup);
            }
        });

        // Filter organizations based on organization type
        document.getElementById('org_type').addEventListener('change', function() {
            const orgTypeId = this.value;
            const orgSelect = document.getElementById('org');

            // Clear current options
            orgSelect.innerHTML = '<option value="">Select</option>';

            if (orgTypeId) {
                // Make AJAX request to fetch organizations
                fetch(`/get-organizations/${orgTypeId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(org => {
                            const option = document.createElement('option');
                            option.value = org.id;
                            option.text = org.name;
                            orgSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching organizations:', error));
            }
        });
    </script>
@endsection
