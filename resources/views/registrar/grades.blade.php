@extends('layouts.app')

@section('title', 'Grade encoding')
@section('eyebrow', 'Registrar / Grades')

@section('content')
    @if ($selectedStudent)
        <section class="panel list-panel">
            <div class="catalog-table-toolbar grade-table-toolbar">
                <div class="grade-table-student">
                    <h2 class="panel-heading-title">{{ $selectedStudent->last_name }}, {{ $selectedStudent->first_name }}</h2>
                    <span class="panel-heading-copy">{{ $selectedStudent->student_number }}</span>
                </div>
            </div>

            @if ($totalSubjectsCount > 0)
                <div class="table-wrap">
                    <table class="responsive-table registrar-grades-table">
                        <thead>
                            <tr>
                                <th scope="col">Subject</th>
                                <th scope="col" class="numeric-cell">Units</th>
                                <th scope="col">Term</th>
                                <th scope="col" class="numeric-cell">Midterm</th>
                                <th scope="col" class="numeric-cell">Final</th>
                                <th scope="col" class="numeric-cell">Average</th>
                                <th scope="col">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gradeRows as $gradeRow)
                                @php
                                    $enrollment = $gradeRow['enrollment'];
                                    $subject = $gradeRow['subject'];
                                    $grade = $gradesBySubject->get("{$enrollment->id}-{$subject->id}");
                                    $rawRemark = $grade?->remarks ?? 'In progress';
                                    $remarkClass = strtolower(str_replace(' ', '-', $rawRemark));
                                @endphp
                                    <tr class="clickable-grade-row"
                                        tabindex="0"
                                        role="button"
                                        aria-label="Encode grades for {{ $subject->code }}"
                                        data-action="{{ route('registrar.grades.update', ['enrollment' => $enrollment->id, 'subject' => $subject->id]) }}"
                                        data-subject-code="{{ $subject->code }}"
                                        data-subject-title="{{ $subject->title }}"
                                        data-subject-units="{{ $subject->units }}"
                                        data-term="{{ $enrollment->school_year }} · {{ $enrollment->semester }}"
                                        data-midterm="{{ $grade?->midterm ?? '' }}"
                                        data-final="{{ $grade?->final_term ?? '' }}"
                                        data-remarks="{{ $grade?->remarks ?? 'In progress' }}">
                                        <td data-label="Subject">
                                            <strong>{{ $subject->code }}</strong>
                                            <small>{{ $subject->title }}</small>
                                        </td>
                                        <td class="numeric-cell" data-label="Units">{{ $subject->units }}</td>
                                        <td class="term-cell" data-label="Term">{{ $enrollment->school_year }} · {{ $enrollment->semester }}</td>
                                        <td class="numeric-cell" data-label="Midterm">{{ $grade?->midterm ?? '—' }}</td>
                                        <td class="numeric-cell" data-label="Final">{{ $grade?->final_term ?? '—' }}</td>
                                        <td class="numeric-cell" data-label="Average"><strong>{{ $grade?->final_grade ?? '—' }}</strong></td>
                                        <td data-label="Remarks">
                                            <span class="status-badge status-{{ $remarkClass }}">
                                                {{ $rawRemark }}
                                            </span>
                                        </td>
                                    </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($gradeRows->hasPages())<div class="pagination-row">{{ $gradeRows->links() }}</div>@endif
            @else
                <div class="empty-state">
                    <span class="empty-mark" aria-hidden="true">↗</span>
                    <h2>No enrolled subjects found.</h2>
                    <p>This student has an approved enrollment but does not have subjects assigned yet.</p>
                </div>
            @endif
        </section>

        {{-- Grade Encoding Modal --}}
        <dialog id="grade-encoding-modal" class="portal-modal" aria-labelledby="grade-modal-title">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-header-copy">
                        <span class="modal-kicker">Grade Encoding</span>
                        <h2 class="modal-title" id="grade-modal-title">Subject code</h2>
                        <p class="modal-subtitle" id="grade-modal-subtitle">Subject title</p>
                    </div>
                    <button type="button" class="modal-close-btn" id="grade-modal-close" aria-label="Close dialog">
                        <x-portal-icon name="close" />
                    </button>
                </div>
                <form id="grade-modal-form" method="post" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="grade-inputs-pair">
                            <div class="grade-input-box">
                                <label class="field-label" for="modal-midterm">Midterm Grade</label>
                                <input class="form-control form-control-number" id="modal-midterm" name="midterm" type="number" min="1" max="5" step="0.01" placeholder="Not entered" aria-describedby="grade-input-help">
                            </div>
                            <div class="grade-input-box">
                                <label class="field-label" for="modal-final-term">Final Grade</label>
                                <input class="form-control form-control-number" id="modal-final-term" name="final_term" type="number" min="1" max="5" step="0.01" placeholder="Not entered" aria-describedby="grade-input-help">
                            </div>
                        </div>

                        <div class="grade-summary-card" aria-live="polite" aria-atomic="true">
                            <div class="grade-computed-display">
                                <span class="grade-summary-label">Average</span>
                                <strong class="grade-computed-value" id="modal-computed-grade">&mdash;</strong>
                            </div>
                            <div class="grade-status-display">
                                <span class="grade-summary-label">Status</span>
                                <span class="status-badge status-under_review" id="modal-computed-badge">In progress</span>
                            </div>
                        </div>
                        <p class="grade-input-help" id="grade-input-help">Enter each term grade from 1.00 to 5.00. Leave blank until graded.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-quiet button-small" id="grade-modal-cancel">Cancel</button>
                        <button type="submit" class="button button-primary button-small">Save grades</button>
                    </div>
                </form>
            </div>
        </dialog>

    @else
        {{-- Student Directory & Filter View --}}
        <section class="panel list-panel">
            <form class="filter-row grades-filter-row" method="get" action="{{ route('registrar.grades') }}">
                <div class="grades-filter-item">
                    <label class="field-label" for="search-student">Search student</label>
                    <input class="form-control form-control-small grades-search-input" id="search-student" name="search" type="search" placeholder="Name or student number" value="{{ $search }}" autocomplete="off">
                </div>
                <div class="grades-filter-item">
                    <label class="field-label" for="program-filter">Program</label>
                    <select class="form-control form-control-small" id="program-filter" name="program_id">
                        <option value="">All programs</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected($selectedProgramId === $program->id)>
                                {{ $program->code }} · {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if ($students && $students->isNotEmpty())
                <div class="table-wrap">
                    <table class="responsive-table student-grades-directory-table">
                        <thead>
                            <tr>
                                <th scope="col">Student</th>
                                <th scope="col">Program</th>
                                <th scope="col">Latest term</th>
                                <th scope="col" class="numeric-cell">Subjects</th>
                                <th scope="col">Grading progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                @php
                                    $latest = $student->enrollments->first();
                                @endphp
                                <tr class="clickable-student-row" data-href="{{ route('registrar.grades', ['student_id' => $student->id]) }}" tabindex="0" role="button" aria-label="Encode grades for {{ $student->last_name }}, {{ $student->first_name }}">
                                    <td data-label="Student">
                                        <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                                        <small>{{ $student->student_number }}</small>
                                    </td>
                                    <td data-label="Program">
                                        <strong>{{ $latest?->program?->code }}</strong>
                                    </td>
                                    <td data-label="Latest term">
                                        {{ $latest?->school_year }} · {{ $latest?->semester }}
                                        @if ($latest?->year_level)
                                            <small>Year {{ $latest->year_level }}</small>
                                        @endif
                                    </td>
                                    <td class="numeric-cell" data-label="Subjects">
                                        <strong>{{ $student->total_subjects }}</strong> <small>enrolled</small>
                                    </td>
                                    <td data-label="Grading progress">
                                        @if ($student->graded_subjects === $student->total_subjects && $student->total_subjects > 0)
                                            <span class="status-badge status-approved">Completed ({{ $student->graded_subjects }}/{{ $student->total_subjects }})</span>
                                        @elseif ($student->graded_subjects > 0)
                                            <span class="status-badge status-reviewed">In progress ({{ $student->graded_subjects }}/{{ $student->total_subjects }})</span>
                                        @else
                                            <span class="status-badge status-submitted">Pending (0/{{ $student->total_subjects }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-row">{{ $students->links() }}</div>
            @else
                <div class="empty-state">
                    <span class="empty-mark" aria-hidden="true">↗</span>
                    <h2>No students found.</h2>
                    <p>There are no students with approved enrollments matching the current filter. Try adjusting your search or program filter.</p>
                </div>
            @endif
        </section>
    @endif
@endsection
