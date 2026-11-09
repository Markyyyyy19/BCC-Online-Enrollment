@extends('layouts.app')

@section('title', 'Student directory')
@section('eyebrow', 'Registrar / Students')

@section('content')
    <section class="panel list-panel">
        <div class="filter-row applications-filter-row">
            <form class="applications-search" method="get" action="{{ route('registrar.students') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
                <div class="applications-search-field">
                    <label class="field-label" for="student-number-search">Student number</label>
                    <input class="form-control form-control-small" type="search" id="student-number-search" name="student_number" value="{{ $studentNumber }}" placeholder="Search by student number" autocomplete="off">
                </div>
                <button class="button button-quiet button-small" type="submit">Search</button>
                @if ($studentNumber !== '')
                    <a class="button button-quiet button-small" href="{{ route('registrar.students', ['per_page' => request('per_page', 20)]) }}">Clear</a>
                @endif
            </form>
        </div>
        <div class="table-wrap">
            <table class="responsive-table student-directory-table">
                <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">Student number</th>
                        <th scope="col">Latest program</th>
                        <th scope="col">Email</th>
                        <th scope="col">Account</th>
                        <th scope="col">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr class="clickable-student-profile-row"
                            tabindex="0"
                            role="button"
                            aria-label="Manage profile for {{ $student->first_name }} {{ $student->last_name }}"
                            data-action="{{ route('registrar.students.update', $student) }}"
                            data-access-action="{{ route('registrar.students.access', $student) }}"
                            data-student-number="{{ $student->student_number ?? '' }}"
                            data-first-name="{{ $student->first_name }}"
                            data-last-name="{{ $student->last_name }}"
                            data-email="{{ $student->email }}"
                            data-program="{{ $student->latest_program_code ?? '' }}"
                            data-is-active="{{ $student->is_active ? '1' : '0' }}"
                            data-joined="{{ $student->created_at?->format('M j, Y') ?? '' }}">
                            <td data-label="Student"><strong>{{ $student->first_name }} {{ $student->last_name }}</strong></td>
                            <td data-label="Student number">{{ $student->student_number ?? '' }}</td>
                            <td data-label="Latest program">{{ $student->latest_program_code ?? '' }}</td>
                            <td data-label="Email">{{ $student->email }}</td>
                            <td data-label="Account">
                                <span class="status-badge {{ $student->is_active ? 'status-approved' : 'status-rejected' }}">
                                    {{ $student->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td data-label="Joined">{{ $student->created_at?->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-cell">{{ $studentNumber !== '' ? 'No students match this student number.' : 'No students have registered yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-row">{{ $students->links() }}</div>
    </section>

    {{-- Student Profile Edit & Access Modal Dialog --}}
    <dialog id="student-edit-modal" class="portal-modal" aria-labelledby="student-modal-title">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-header-copy">
                    <span class="modal-kicker">Student Profile</span>
                    <h2 class="modal-title" id="student-modal-title">Student Name</h2>
                    <p class="modal-subtitle" id="student-modal-subtitle">Student number · Program</p>
                </div>
                <button type="button" class="modal-close-btn" id="student-modal-close" aria-label="Close dialog">
                    <x-portal-icon name="close" />
                </button>
            </div>

            <form id="student-edit-form" method="post" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="student-modal-grid">
                        <div class="field-item">
                            <label class="field-label" for="student-modal-number">Student number</label>
                            <input class="form-control" id="student-modal-number" name="student_number" maxlength="30" placeholder="e.g. 2026-0001">
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="student-modal-email">Email address</label>
                            <input class="form-control" id="student-modal-email" name="email" type="email" required>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="student-modal-first-name">First name</label>
                            <input class="form-control" id="student-modal-first-name" name="first_name" required>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="student-modal-last-name">Last name</label>
                            <input class="form-control" id="student-modal-last-name" name="last_name" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="justify-content: space-between;">
                    <div>
                        <button type="button" class="button button-quiet button-small" id="student-access-btn">Deactivate account</button>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="button button-quiet button-small" id="student-modal-cancel">Cancel</button>
                        <button type="submit" class="button button-primary button-small">Save profile</button>
                    </div>
                </div>
            </form>

            <form id="student-access-form" method="post" action="" style="display: none;">
                @csrf
                @method('PATCH')
            </form>
        </div>
    </dialog>
@endsection
