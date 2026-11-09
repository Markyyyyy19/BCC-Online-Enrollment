@extends('layouts.app')
@section('title', 'New enrollment')
@section('content')
    <div class="enrollment-page">
        <section class="panel enrollment-details" aria-labelledby="enrollment-form-title">
            <h1 id="enrollment-form-title">Enrollment form</h1>
            <form method="get" action="{{ route('student.enrollments.create') }}" id="curriculum-form">
                <input type="hidden" name="school_year" id="filter-school-year" value="{{ old('school_year', $schoolYear) }}">
                <div class="enrollment-details-grid">
                    <div class="enrollment-field">
                        <label for="student_name">Full name</label>
                        <input class="form-control" id="student_name" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}" readonly>
                    </div>
                    <div class="enrollment-field">
                        <label for="filter-program">Course</label>
                        <select class="form-control" id="filter-program" name="program_id" required>
                            <option value="">Select a course</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}" @selected($selectedProgram?->id === $program->id)>{{ $program->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="enrollment-field">
                        <label for="filter-major">Major</label>
                        <select class="form-control" id="filter-major" name="major_id" @disabled($majors->isEmpty()) @required($majors->isNotEmpty())>
                            <option value="">{{ ! $selectedProgram ? 'Select a course first' : ($majors->isEmpty() ? 'Not applicable' : 'Select a major') }}</option>
                            @foreach ($majors as $major)
                                <option value="{{ $major->id }}" @selected($selectedMajorId === $major->id)>{{ $major->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="enrollment-field">
                        <label for="filter-year">Year level</label>
                        <select class="form-control" id="filter-year" name="year_level">
                            @foreach (range(1, 6) as $year)
                                <option value="{{ $year }}" @selected($selectedYearLevel === $year)>{{ $year }}{{ $year === 1 ? 'st' : ($year === 2 ? 'nd' : ($year === 3 ? 'rd' : 'th')) }} year</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="enrollment-field">
                        <label for="filter-semester">Semester</label>
                        <select class="form-control" id="filter-semester" name="semester">
                            @foreach (['1st', '2nd', 'Summer'] as $semester)
                                <option value="{{ $semester }}" @selected($selectedSemester === $semester)>{{ $semester }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="enrollment-field">
                        <label for="school_year">Academic year</label>
                        <input class="form-control" id="school_year" name="school_year" form="enrollment-submit" value="{{ old('school_year', $schoolYear) }}" pattern="\d{4}-\d{4}" title="Use YYYY-YYYY, for example 2026-2027" placeholder="YYYY-YYYY" required>
                    </div>
                    <div class="curriculum-actions"><button class="button button-secondary" type="submit"><x-portal-icon name="subjects" />Load subjects</button></div>
                </div>
            </form>
        </section>

        <form method="post" action="{{ route('student.enrollments.store') }}" id="enrollment-submit" class="panel enrollment-subject-panel">
            @csrf
            <input type="hidden" name="program_id" value="{{ $selectedProgram?->id }}">
            <input type="hidden" name="major_id" value="{{ $selectedMajorId }}">
            <input type="hidden" name="semester" value="{{ $selectedSemester }}">
            <input type="hidden" name="year_level" value="{{ $selectedYearLevel }}">
            <div class="enrollment-subject-toolbar">
                <h2 id="enrollment-subjects-title">Subjects</h2>
                @if ($subjects->isNotEmpty())
                    <button class="button button-secondary button-small" id="add-all-subjects" type="button" hidden>Add all subjects</button>
                @endif
            </div>
            <p class="curriculum-notice" id="curriculum-notice" role="status" hidden>Course or term changed. Load subjects to continue.</p>
            @if ($subjects->isNotEmpty())
                <div class="subject-picker-row" hidden>
                    <div class="enrollment-field">
                        <label for="subject-picker">Add a subject</label>
                        <select class="form-control" id="subject-picker">
                            <option value="">Select a subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->code }} — {{ $subject->title }} ({{ $subject->units }} units)</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="button button-primary" id="add-subject" type="button" disabled><x-portal-icon name="plus" />Add</button>
                </div>
            @endif
            <div class="table-wrap">
                <table class="responsive-table enrollment-subject-table" aria-labelledby="enrollment-subjects-title">
                    <thead><tr><th scope="col">Code</th><th scope="col">Descriptive Title</th><th scope="col" class="numeric-cell">Units</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
                    <tbody>
                        @forelse ($subjects as $subject)
                            @php($subjectIsSelected = in_array($subject->id, old('subject_ids', [])))
                            <tr>
                                <td data-label="Code"><strong>{{ $subject->code }}</strong></td>
                                <td data-label="Descriptive Title">{{ $subject->title }}</td>
                                <td class="numeric-cell" data-label="Units">{{ $subject->units }}</td>
                                <td data-label="Status"><span class="status-badge {{ $subjectIsSelected ? 'status-approved' : 'subject-available' }}" data-subject-status>{{ $subjectIsSelected ? 'Selected' : 'Available' }}</span></td>
                                <td class="subject-action-cell" data-label="Action"><label class="subject-select-label"><input class="subject-select-checkbox" id="subject-{{ $subject->id }}" type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" data-units="{{ $subject->units }}" aria-label="Select {{ $subject->code }}" @checked($subjectIsSelected)><span>Select</span></label><button class="button button-quiet button-small remove-subject" type="button" aria-label="Remove {{ $subject->code }}" hidden>Remove</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-cell">
                                <div class="subject-empty"><x-portal-icon name="subjects" />
                                    @if ($selectedProgram && $majors->isNotEmpty() && ! $selectedMajorId)
                                        <p>Select a major, then load subjects.</p>
                                    @elseif ($selectedProgram)
                                        <p>No subjects available for this course and term.</p>
                                    @else
                                        <p>Select your course and term to load subjects.</p>
                                    @endif
                                </div>
                            </td></tr>
                        @endforelse
                        @if ($subjects->isNotEmpty())
                            <tr id="subjects-empty" hidden><td colspan="5" class="empty-cell"><div class="subject-empty"><x-portal-icon name="subjects" /><p>No subjects added yet. Choose a subject above.</p></div></td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="enrollment-form-footer">
                <p class="selection-total" id="selection-total" role="status" hidden></p>
                <button class="button button-primary" id="forward-enrollment" type="submit" @disabled($subjects->isEmpty())>Forward for approval<x-portal-icon name="arrow" /></button>
            </div>
        </form>
    </div>
    <script src="{{ asset('js/enrollment.js') }}" defer></script>
@endsection
