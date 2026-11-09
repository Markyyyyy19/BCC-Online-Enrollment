@extends('layouts.app')
@section('title', 'My applications')
@section('content')
    @php($student = auth()->user())

    <section class="panel list-panel student-applications-panel" aria-label="Enrollment applications">
        <div class="catalog-table-toolbar">
            <h2 class="panel-heading-title catalog-table-title" id="applications-table-title">Applications</h2>
            <a class="button button-primary catalog-add-button" href="{{ route('student.enrollments.create') }}"><x-portal-icon name="plus" />New application</a>
        </div>
        @if ($enrollments->isNotEmpty())
            <div class="table-wrap">
                <table class="responsive-table applications-table student-applications" aria-labelledby="applications-table-title">
                    <thead><tr><th scope="col">Student number</th><th scope="col">Student</th><th scope="col">Program</th><th scope="col">Academic year</th><th scope="col">Semester</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            <tr>
                                <td data-label="Student number"><span class="student-number">{{ $student->student_number ?: '' }}</span></td>
                                <td data-label="Student"><strong>{{ trim($student->first_name.' '.$student->last_name) }}</strong></td>
                                <td data-label="Program"><strong>{{ $enrollment->program?->code ?? '' }}</strong>@if ($enrollment->major?->name)<small>{{ $enrollment->major->name }}</small>@endif</td>
                                <td data-label="Academic year">{{ $enrollment->school_year }}</td>
                                <td data-label="Semester">{{ $enrollment->semester }}</td>
                                <td data-label="Status"><span class="status-badge status-{{ $enrollment->status }}">{{ ucfirst(str_replace('_', ' ', $enrollment->status)) }}</span>@if ($enrollment->registrar_note)<small class="registrar-note">{{ $enrollment->registrar_note }}</small>@endif</td>
                                <td class="table-action-cell" data-label="Action">
                                    @if ($enrollment->status === 'approved')
                                        <a class="button button-quiet button-small" href="{{ route('student.grades') }}">View grades <x-portal-icon name="arrow" /></a>
                                    @elseif (in_array($enrollment->status, ['draft', 'submitted'], true))
                                        <form method="post" action="{{ route('student.enrollments.withdraw', $enrollment) }}">@csrf @method('PATCH')<button class="button button-quiet button-small" type="submit">Withdraw</button></form>
                                    @else
                                        <span class="muted" aria-label="No action available"></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($enrollments->hasPages())<div class="pagination-row">{{ $enrollments->links() }}</div>@endif
        @else
            <div class="empty-state"><span class="empty-mark"><x-portal-icon name="applications" /></span><h2>No applications yet</h2><p>Start a new application to enroll for your next term.</p></div>
        @endif
    </section>
@endsection
