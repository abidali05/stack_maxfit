@extends('layouts.app')
@section('title', 'Edit Competitions')
@section('content')
    <div class="px-4 pt-4 container-fluid" style="min-height: 82.5vh">
        <form action="{{ route('competitions.update', $competition->id) }}" method="POST" enctype="multipart/form-data" id="competitionForm">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="p-4 rounded bg-light">
                        <div class="mb-4 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">Edit Competition</h6>
                        </div>

                        {{-- Success/Error Alerts --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row g-3">
                            <!-- Shared Fields -->
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $competition->name) }}" required>
                                <div class="text-danger error-message" id="name-error"></div>
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Age Group</label>
                                <input type="number" class="form-control" name="age_group" id="age_group" value="{{ old('age_group', $competition->age_group) }}" required min="1">
                                <div class="text-danger error-message" id="age_group-error"></div>
                                @error('age_group')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status">
                                    <option value="active" {{ old('status', $competition->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $competition->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="text-danger error-message" id="status-error"></div>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" value="{{ old('country', $competition->country) }}" required>
                                <div class="text-danger error-message" id="country-error"></div>
                                @error('country')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time Allowed (minutes)</label>
                                <input type="number" class="form-control" name="time_allowed" id="time_allowed" min="1" value="{{ old('time_allowed', $competition->time_allowed) }}" required>
                                <div class="text-danger error-message" id="time_allowed-error"></div>
                                @error('time_allowed')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Select Genz</label>
                                <select class="form-control" id="genz" disabled>
                                    <option value="">Select</option>
                                    <option value="motherfits" {{ old('genz', $competition->genz) === 'motherfits' ? 'selected' : '' }}>Mother Fit</option>
                                    <option value="fatherfits" {{ old('genz', $competition->genz) === 'fatherfits' ? 'selected' : '' }}>Father Fit</option>
                                </select>
                                <input type="hidden" name="genz" id="genz_hidden" value="{{ old('genz', $competition->genz) }}">
                                <div class="text-danger error-message" id="genz-error"></div>
                                @error('genz')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Organization Type</label>
                                <select class="form-control org-type-select" name="org_type" id="org_type">
                                    <option value="">Select</option>
                                    @foreach ($organizationTypes as $organizationType)
                                        <option value="{{ $organizationType->id }}" {{ old('org_type', $competition->org_type) == $organizationType->id ? 'selected' : '' }}>
                                            {{ $organizationType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-message" id="org_type-error"></div>
                                @error('org_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Organization</label>
                                <select class="form-control org-select" name="org" id="org">
                                    <option value="">Select</option>
                                    <!-- Options populated by AJAX -->
                                </select>
                                <div class="text-danger error-message" id="org-error"></div>
                                @error('org')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">YouTube Video Links</label>
                                <div id="youtube-links-wrapper">
                                    @if (old('youtube_links', $competition->videos->pluck('video_file')->toArray()))
                                        @foreach (old('youtube_links', $competition->videos->pluck('video_file')->toArray()) as $index => $link)
                                            <div class="d-flex mb-2 youtube-link-row">
                                                <input type="url" class="form-control me-2" name="youtube_links[]" value="{{ $link }}" placeholder="https://www.youtube.com/watch?v=..." required>
                                                <button type="button" class="btn btn-danger btn-sm remove-link">&times;</button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="d-flex mb-2 youtube-link-row">
                                            <input type="url" class="form-control me-2" name="youtube_links[]" placeholder="https://www.youtube.com/watch?v=..." required>
                                            <button type="button" class="btn btn-danger btn-sm remove-link">&times;</button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="add-link">+ Add another link</button>
                                <div class="text-danger error-message" id="youtube_links-error"></div>
                                @error('youtube_links.*')
                                    <span class="text-danger d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Repeatable Competition Details -->
                        <div id="competition-container" class="mt-4">
                            @foreach ($competition->details as $index => $detail)
                                <div class="competition-field border p-3 mb-3 rounded">
                                    <h6>Competition Details {{ $index + 1 }}</h6>
                                    <input type="hidden" name="detail_ids[]" value="{{ $detail->id }}">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Coach</label>
                                            <select class="form-control" name="coach_id[]" required>
                                                <option value="">Select</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}" {{ old('coach_id.' . $index, $detail->coach_id) == $coach->id ? 'selected' : '' }}>
                                                        {{ $coach->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-danger error-message" id="coach_id-{{ $index }}-error"></div>
                                            @error('coach_id.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="cities[]" value="{{ old('cities.' . $index, $detail->city) }}" required>
                                            <div class="text-danger error-message" id="cities-{{ $index }}-error"></div>
                                            @error('cities.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" class="form-control" name="start_date[]" value="{{ old('start_date.' . $index, $detail->start_date) }}" required>
                                            <div class="text-danger error-message" id="start_date-{{ $index }}-error"></div>
                                            @error('start_date.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">End Date</label>
                                            <input type="date" class="form-control" name="end_date[]" value="{{ old('end_date.' . $index, $detail->end_date) }}" required>
                                            <div class="text-danger error-message" id="end_date-{{ $index }}-error"></div>
                                            @error('end_date.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Start Time</label>
                                            <input type="time" class="form-control start-time" name="start_time[]" value="{{ old('start_time.' . $index, optional($detail->start_time)->format('H:i')) }}" required>
                                            <div class="text-danger error-message" id="start_time-{{ $index }}-error"></div>
                                            @error('start_time.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">End Time</label>
                                            <input type="time" class="form-control end-time" name="end_time[]" value="{{ old('end_time.' . $index, optional($detail->end_time)->format('H:i')) }}" required readonly>
                                            <small class="text-muted">Calculated automatically based on start time and time allowed</small>
                                            <div class="text-danger error-message" id="end_time-{{ $index }}-error"></div>
                                            @error('end_time.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Image</label>
                                            <input type="file" class="form-control" name="image[]" accept="image/*">
                                            @if ($detail->image)
                                                <small class="text-muted">Current: <a href="{{ Storage::url($detail->image) }}" target="_blank">View Image</a></small>
                                            @endif
                                            <div class="text-danger error-message" id="image-{{ $index }}-error"></div>
                                            @error('image.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description[]" rows="4">{{ old('description.' . $index, $detail->description) }}</textarea>
                                            <div class="text-danger error-message" id="description-{{ $index }}-error"></div>
                                            @error('description.' . $index)
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-danger btn-sm remove-competition" {{ $index == 0 ? 'style="display: none;"' : '' }}>Remove Competition</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if ($competition->details->isEmpty())
                                <div class="competition-field border p-3 mb-3 rounded">
                                    <h6>Competition Details</h6>
                                    <div class="row g-3">
                                        <input type="hidden" name="detail_ids[]" value="">
                                        <div class="col-md-6">
                                            <label class="form-label">Coach</label>
                                            <select class="form-control" name="coach_id[]" required>
                                                <option value="">Select</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}" {{ old('coach_id.0') == $coach->id ? 'selected' : '' }}>
                                                        {{ $coach->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-danger error-message" id="coach_id-0-error"></div>
                                            @error('coach_id.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="cities[]" value="{{ old('cities.0') }}" required>
                                            <div class="text-danger error-message" id="cities-0-error"></div>
                                            @error('cities.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" class="form-control" name="start_date[]" value="{{ old('start_date.0') }}" required>
                                            <div class="text-danger error-message" id="start_date-0-error"></div>
                                            @error('start_date.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">End Date</label>
                                            <input type="date" class="form-control" name="end_date[]" value="{{ old('end_date.0') }}" required>
                                            <div class="text-danger error-message" id="end_date-0-error"></div>
                                            @error('end_date.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Start Time</label>
                                            <input type="time" class="form-control start-time" name="start_time[]" value="{{ old('start_time.0') }}" required>
                                            <div class="text-danger error-message" id="start_time-0-error"></div>
                                            @error('start_time.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">End Time</label>
                                            <input type="time" class="form-control end-time" name="end_time[]" value="{{ old('end_time.0') }}" required readonly>
                                            <small class="text-muted">Calculated automatically based on start time and time allowed</small>
                                            <div class="text-danger error-message" id="end_time-0-error"></div>
                                            @error('end_time.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Image</label>
                                            <input type="file" class="form-control" name="image[]" accept="image/*">
                                            <div class="text-danger error-message" id="image-0-error"></div>
                                            @error('image.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description[]" rows="4">{{ old('description.0') }}</textarea>
                                            <div class="text-danger error-message" id="description-0-error"></div>
                                            @error('description.0')
                                                <span class="text-danger">{{ $message }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-danger btn-sm remove-competition" style="display: none;">Remove Competition</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-competition">Add Another Competition</button>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit" id="submitCompetition">Update Competition</button>
                            <a href="{{ route('competitions.index') }}" class="btn btn-outline-danger">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Debugging: Log when script runs
        console.log('Edit competition script loaded');

        // Function to update Genz value based on age group
        function updateGenzValue(ageGroup) {
            console.log('Updating genz value for age group:', ageGroup);
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

        // Function to calculate end time based on start time and time allowed
        function calculateEndTime(startTimeInput) {
            console.log('Calculating end time for:', startTimeInput.value);
            const timeAllowed = parseInt(document.getElementById('time_allowed').value);
            const endTimeInput = startTimeInput.closest('.competition-field').querySelector('.end-time');
            if (!startTimeInput.value || isNaN(timeAllowed) || timeAllowed <= 0) {
                endTimeInput.value = '';
                return;
            }
            const [hours, minutes] = startTimeInput.value.split(':').map(Number);
            const startDate = new Date();
            startDate.setHours(hours, minutes, 0, 0);
            startDate.setMinutes(startDate.getMinutes() + timeAllowed);
            const endHours = String(startDate.getHours()).padStart(2, '0');
            const endMinutes = String(startDate.getMinutes()).padStart(2, '0');
            endTimeInput.value = `${endHours}:${endMinutes}`;
        }

        // Set initial Genz value and update end times on page load
        window.addEventListener('load', function() {
            console.log('Window loaded');
            const ageGroupInput = document.getElementById('age_group');
            const ageGroup = parseInt(ageGroupInput.value) || 0;
            updateGenzValue(ageGroup);

            // Trigger org_type change for existing fields
            const orgTypeSelect = document.getElementById('org_type');
            if (orgTypeSelect && orgTypeSelect.value) {
                orgTypeSelect.dispatchEvent(new Event('change'));
            }

            // Update end time for existing start_time fields
            document.querySelectorAll('.start-time').forEach(input => {
                if (input.value) {
                    calculateEndTime(input);
                }
            });

            updateRemoveButtons();
        });

        // Update Genz value on age_group input change
        document.getElementById('age_group').addEventListener('input', function() {
            const ageGroup = parseInt(this.value) || 0;
            updateGenzValue(ageGroup);
        });

        // Update end time on time_allowed or start_time change
        document.getElementById('competition-container').addEventListener('input', function(e) {
            if (e.target.classList.contains('start-time')) {
                calculateEndTime(e.target);
            }
        });
        document.getElementById('time_allowed').addEventListener('input', function() {
            document.querySelectorAll('.start-time').forEach(input => {
                if (input.value) {
                    calculateEndTime(input);
                }
            });
        });

        // Filter organizations based on organization type
        function attachOrgTypeListener(orgTypeSelect, orgSelect) {
            orgTypeSelect.addEventListener('change', function() {
                console.log('Org type changed:', this.value);
                const orgTypeId = this.value;
                orgSelect.innerHTML = '<option value="">Select</option>';
                if (orgTypeId) {
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
                            if (org.id == '{{ old('org', $competition->org) }}') {
                                option.selected = true;
                            }
                            orgSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching organizations:', error));
                }
            });
        }

        // Attach org_type listener to existing org_type select
        const initialOrgTypeSelect = document.getElementById('org_type');
        const initialOrgSelect = document.getElementById('org');
        if (initialOrgTypeSelect && initialOrgSelect) {
            attachOrgTypeListener(initialOrgTypeSelect, initialOrgSelect);
        }

        // Add new competition field set
        document.getElementById('add-competition').addEventListener('click', function() {
            console.log('Add Another Competition clicked');
            const competitionContainer = document.getElementById('competition-container');
            const competitionField = document.createElement('div');
            competitionField.className = 'competition-field border p-3 mb-3 rounded';
            competitionField.innerHTML = `
                <h6>Competition Details</h6>
                <div class="row g-3">
                    <input type="hidden" name="detail_ids[]" value="">
                    <div class="col-md-6">
                        <label class="form-label">Coach</label>
                        <select class="form-control" name="coach_id[]" required>
                            <option value="">Select</option>
                            @foreach ($coaches as $coach)
                                <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger error-message" id="coach_id-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="cities[]" required>
                        <div class="text-danger error-message" id="cities-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date[]" required>
                        <div class="text-danger error-message" id="start_date-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date[]" required>
                        <div class="text-danger error-message" id="end_date-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control start-time" name="start_time[]" required>
                        <div class="text-danger error-message" id="start_time-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control end-time" name="end_time[]" required readonly>
                        <small class="text-muted">Calculated automatically based on start time and time allowed</small>
                        <div class="text-danger error-message" id="end_time-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image[]" accept="image/*">
                        <div class="text-danger error-message" id="image-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description[]" rows="4"></textarea>
                        <div class="text-danger error-message" id="description-${competitionContainer.children.length}-error"></div>
                    </div>
                    <div class="col-md-12">
                        <button type="button" class="btn btn-danger btn-sm remove-competition">Remove Competition</button>
                    </div>
                </div>
            `;
            competitionContainer.appendChild(competitionField);

            const newStartTimeInput = competitionField.querySelector('.start-time');
            newStartTimeInput.addEventListener('input', function() {
                calculateEndTime(this);
            });

            updateRemoveButtons();
        });

        // Function to update visibility of remove buttons
        function updateRemoveButtons() {
            console.log('Updating remove buttons');
            const competitionContainer = document.getElementById('competition-container');
            const competitionFields = competitionContainer.getElementsByClassName('competition-field');
            Array.from(competitionFields).forEach((field, index) => {
                const removeButton = field.querySelector('.remove-competition');
                if (removeButton) {
                    removeButton.style.display = index === 0 ? 'none' : 'inline-block';
                }
            });
        }

        // Event delegation for remove buttons
        document.getElementById('competition-container').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-competition')) {
                console.log('Remove competition clicked');
                e.target.closest('.competition-field').remove();
                updateRemoveButtons();
            }
        });

        // YouTube link management
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded for YouTube links');
            const wrapper = document.getElementById('youtube-links-wrapper');
            const addLinkBtn = document.getElementById('add-link');

            addLinkBtn.addEventListener('click', function() {
                console.log('Add YouTube link clicked');
                const div = document.createElement('div');
                div.classList.add('d-flex', 'mb-2', 'youtube-link-row');
                div.innerHTML = `
                    <input type="url" class="form-control me-2" name="youtube_links[]" placeholder="https://www.youtube.com/watch?v=..." required>
                    <button type="button" class="btn btn-danger btn-sm remove-link">&times;</button>
                `;
                wrapper.appendChild(div);
            });

            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-link')) {
                    console.log('Remove YouTube link clicked');
                    e.target.closest('.youtube-link-row').remove();
                }
            });
        });

        // Client-side validation
        document.getElementById('submitCompetition').addEventListener('click', function(e) {
            e.preventDefault();
            let isValid = true;
            const errorElements = document.getElementsByClassName('error-message');

            // Clear previous errors
            Array.from(errorElements).forEach(element => {
                element.textContent = '';
            });

            // Validate main fields
            const name = document.querySelector('input[name="name"]');
            if (!name.value.trim()) {
                document.getElementById('name-error').textContent = 'Name is required.';
                isValid = false;
            }

            const ageGroup = document.getElementById('age_group');
            if (!ageGroup.value || ageGroup.value < 1) {
                document.getElementById('age_group-error').textContent = 'Age group must be at least 1.';
                isValid = false;
            }

            const status = document.querySelector('select[name="status"]');
            if (!status.value) {
                document.getElementById('status-error').textContent = 'Status is required.';
                isValid = false;
            }

            const country = document.querySelector('input[name="country"]');
            if (!country.value.trim()) {
                document.getElementById('country-error').textContent = 'Country is required.';
                isValid = false;
            }

            const timeAllowed = document.getElementById('time_allowed');
            if (!timeAllowed.value || timeAllowed.value < 1) {
                document.getElementById('time_allowed-error').textContent = 'Time allowed must be at least 1 minute.';
                isValid = false;
            }

            const genz = document.getElementById('genz_hidden');
            if (!genz.value) {
                document.getElementById('genz-error').textContent = 'Genz is required.';
                isValid = false;
            }

            const orgType = document.querySelector('select[name="org_type"]');
            if (!orgType.value) {
                document.getElementById('org_type-error').textContent = 'Organization type is required.';
                isValid = false;
            }

            const org = document.querySelector('select[name="org"]');
            if (!org.value) {
                document.getElementById('org-error').textContent = 'Organization is required.';
                isValid = false;
            }

            // Validate YouTube links
            const youtubeLinks = document.querySelectorAll('input[name="youtube_links[]"]');
            youtubeLinks.forEach((link, index) => {
                if (link.value && !isValidUrl(link.value)) {
                    document.getElementById('youtube_links-error').textContent = 'One or more YouTube links are invalid.';
                    isValid = false;
                }
            });

            // Validate competition fields
            const competitionFields = document.querySelectorAll('.competition-field');
            competitionFields.forEach((field, fieldIndex) => {
                const coach = field.querySelector('select[name="coach_id[]"]');
                if (!coach.value) {
                    document.getElementById(`coach_id-${fieldIndex}-error`).textContent = 'Coach is required.';
                    isValid = false;
                }

                const city = field.querySelector('input[name="cities[]"]');
                if (!city.value.trim()) {
                    document.getElementById(`cities-${fieldIndex}-error`).textContent = 'City is required.';
                    isValid = false;
                }

                const startDate = field.querySelector('input[name="start_date[]"]');
                if (!startDate.value) {
                    document.getElementById(`start_date-${fieldIndex}-error`).textContent = 'Start date is required.';
                    isValid = false;
                }

                const endDate = field.querySelector('input[name="end_date[]"]');
                if (!endDate.value) {
                    document.getElementById(`end_date-${fieldIndex}-error`).textContent = 'End date is required.';
                    isValid = false;
                }

                const startTime = field.querySelector('input[name="start_time[]"]');
                if (!startTime.value) {
                    document.getElementById(`start_time-${fieldIndex}-error`).textContent = 'Start time is required.';
                    isValid = false;
                }

                const endTime = field.querySelector('input[name="end_time[]"]');
                if (!endTime.value) {
                    document.getElementById(`end_time-${fieldIndex}-error`).textContent = 'End time is required.';
                    isValid = false;
                }

                // Image and description are optional, no validation needed here
            });

            // Custom validation for date and time consistency
            competitionFields.forEach((field, fieldIndex) => {
                const startDate = field.querySelector('input[name="start_date[]"]');
                const endDate = field.querySelector('input[name="end_date[]"]');
                if (startDate.value && endDate.value && new Date(endDate.value) < new Date(startDate.value)) {
                    document.getElementById(`end_date-${fieldIndex}-error`).textContent = 'End date must be on or after start date.';
                    isValid = false;
                }

                const startTime = field.querySelector('input[name="start_time[]"]');
                const endTime = field.querySelector('input[name="end_time[]"]');
                if (startDate.value === endDate.value && startTime.value && endTime.value) {
                    const start = new Date(`1970-01-01T${startTime.value}:00`);
                    const end = new Date(`1970-01-01T${endTime.value}:00`);
                    if (end <= start) {
                        document.getElementById(`end_time-${fieldIndex}-error`).textContent = 'End time must be after start time.';
                        isValid = false;
                    }
                }
            });

            if (isValid) {
                document.getElementById('competitionForm').submit();
            }
        });

        // Simple URL validation function
        function isValidUrl(string) {
            try {
                new URL(string);
                return string.startsWith('http://') || string.startsWith('https://');
            } catch (_) {
                return false;
            }
        }
    </script>
@endsection
