@extends('layouts.app')

@section('title', 'Applications')
@section('eyebrow', 'Registrar / Applications')

@section('content')
    <section class="panel list-panel">
        <div class="filter-row applications-filter-row">
            <form class="applications-search" method="get" action="{{ route('registrar.applications.index') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
                <div class="applications-search-field">
                    <label class="field-label" for="application-student-search">Student ID or name</label>
                    <input class="form-control form-control-small" id="application-student-search" name="search" value="{{ $search }}" placeholder="Search ID, name, or email" autocomplete="off">
                </div>
                <div class="applications-search-field">
                    <label class="field-label" for="application-status-filter">Status</label>
                    <select class="form-control form-control-small" id="application-status-filter" name="status">
                        @foreach (['all' => 'All applications', 'submitted' => 'Submitted', 'under_review' => 'Under review', 'approved' => 'Approved', 'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn'] as $status => $label)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="button button-quiet button-small" type="submit">Search</button>
            </form>
        </div>
        @if ($enrollments->isNotEmpty())
            <div class="table-wrap"><table class="responsive-table applications-table registrar-applications"><thead><tr><th scope="col">Student</th><th scope="col">Program & term</th><th scope="col">Submitted</th><th scope="col">Status</th><th scope="col" class="table-action-cell">Review</th></tr></thead><tbody>
                @foreach ($enrollments as $enrollment)
                    <tr><td data-label="Student"><strong>{{ $enrollment->student?->first_name }} {{ $enrollment->student?->last_name }}</strong><small>{{ $enrollment->student?->student_number }} · {{ $enrollment->student?->email }}</small></td><td data-label="Program & term"><strong>{{ $enrollment->program?->code }}{{ $enrollment->major?->name ? ' · '.$enrollment->major->name : '' }}</strong><small>{{ $enrollment->school_year }} · {{ $enrollment->semester }} · Year {{ $enrollment->year_level }} · {{ $enrollment->subjects->count() }} subjects</small></td><td data-label="Submitted">{{ $enrollment->submitted_at?->format('M j, Y') }}</td><td data-label="Status"><span class="status-badge status-{{ $enrollment->status }}">{{ ucfirst(str_replace('_', ' ', $enrollment->status)) }}</span></td><td data-label="Review" class="table-action-cell"><button type="button" class="button button-quiet button-small" data-create-modal="review-{{ $enrollment->id }}" aria-haspopup="dialog" aria-controls="review-{{ $enrollment->id }}">Review</button></td></tr>
                @endforeach
            </tbody></table></div>
            @if ($enrollments->hasPages())<div class="pagination-row">{{ $enrollments->links() }}</div>@endif
        @else
            <div class="empty-state"><span class="empty-mark"><x-portal-icon name="applications" /></span><h2>No applications in this view</h2><p>New applications will appear here. Try another filter to see more.</p></div>
        @endif
    </section>
    @foreach ($enrollments as $enrollment)
        <dialog id="review-{{ $enrollment->id }}" class="portal-modal" aria-labelledby="review-title-{{ $enrollment->id }}">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-header-copy">
                        <h2 class="modal-title" id="review-title-{{ $enrollment->id }}">Review application</h2>
                        <p class="modal-subtitle">{{ $enrollment->student?->first_name }} {{ $enrollment->student?->last_name }} · {{ $enrollment->program?->code }}</p>
                    </div>
                    <button type="button" class="modal-close-btn" data-create-modal-close aria-label="Close dialog"><x-portal-icon name="close" /></button>
                </div>
                <div class="modal-body application-review-details">
                    <p><strong>Term</strong><span>{{ $enrollment->school_year }} · {{ $enrollment->semester }} · Year {{ $enrollment->year_level }}</span></p>
                    <p><strong>Subjects</strong><span>{{ $enrollment->subjects->count() }} selected</span></p>
                    <p><strong>Current status</strong><span>{{ ucfirst(str_replace('_', ' ', $enrollment->status)) }}</span></p>
                </div>
                <form method="post" action="{{ route('registrar.applications.review', $enrollment) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-footer application-review-actions">
                        <button class="button button-quiet button-small" type="button" data-create-modal-close>Cancel</button>
                        <button class="button button-reject button-small" type="submit" name="status" value="rejected">Reject</button>
                        <button class="button button-primary button-small" type="submit" name="status" value="approved">Approve</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endforeach
@endsection
