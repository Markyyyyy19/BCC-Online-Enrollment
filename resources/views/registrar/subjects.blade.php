@extends('layouts.app')

@section('title', 'Subject catalog')
@section('eyebrow', 'Registrar / Subjects')

@section('content')
    <section class="panel list-panel">
        <div class="catalog-table-toolbar">
            <form class="filter-row subjects-filter-row" method="get" action="{{ route('registrar.subjects') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
                <div class="subjects-filter-item">
                    <label class="field-label" for="program-filter">Program</label>
                    <select class="form-control form-control-small" id="program-filter" name="program_id">
                        <option value="">All programs</option>
                        @foreach ($programs as $prog)
                            <option value="{{ $prog->id }}" @selected(($selectedProgramId ?? null) == $prog->id)>{{ $prog->code }} · {{ $prog->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="subjects-filter-item">
                    <label class="field-label" for="year-filter">Year level</label>
                    <select class="form-control form-control-small" id="year-filter" name="year_level">
                        <option value="">All years</option>
                        @foreach (range(1, 4) as $yr)
                            <option value="{{ $yr }}" @selected(($selectedYearLevel ?? null) == $yr)>Year {{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="subjects-filter-item">
                    <label class="field-label" for="semester-filter">Semester</label>
                    <select class="form-control form-control-small" id="semester-filter" name="semester">
                        <option value="">All semesters</option>
                        <option value="1st" @selected(($selectedSemester ?? null) === '1st')>1st semester</option>
                        <option value="2nd" @selected(($selectedSemester ?? null) === '2nd')>2nd semester</option>
                        <option value="Summer" @selected(($selectedSemester ?? null) === 'Summer')>Summer</option>
                    </select>
                </div>
            </form>
            <button type="button" class="button button-primary catalog-add-button" data-create-modal="add-subject-modal" aria-haspopup="dialog" aria-controls="add-subject-modal">Add a subject <span aria-hidden="true">&#43;</span></button>
        </div>

        <div class="table-wrap" id="subjects-table-wrap">
            <table class="responsive-table registrar-subjects-table">
                <thead>
                    <tr>
                        <th scope="col">Subject</th>
                        <th scope="col">Program</th>
                        <th scope="col">Year level</th>
                        <th scope="col">Semester</th>
                        <th scope="col" class="numeric-cell">Units</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        <tr class="clickable-subject-row"
                            tabindex="0"
                            role="button"
                            aria-label="Edit subject {{ $subject->code }} - {{ $subject->title }}"
                            data-action="{{ route('registrar.subjects.update', $subject) }}"
                            data-toggle-action="{{ route('registrar.subjects.toggle', $subject) }}"
                            data-code="{{ $subject->code }}"
                            data-title="{{ $subject->title }}"
                            data-program-id="{{ $subject->program_id }}"
                            data-major-id="{{ $subject->major_id }}"
                            data-units="{{ $subject->units }}"
                            data-year-level="{{ $subject->year_level }}"
                            data-semester="{{ $subject->semester }}"
                            data-is-active="{{ $subject->is_active ? '1' : '0' }}">
                            <td data-label="Subject"><strong>{{ $subject->code }}</strong><small>{{ $subject->title }}</small></td>
                            <td data-label="Program"><strong>{{ $subject->program?->code ?? '' }}</strong></td>
                            <td data-label="Year level">Year {{ $subject->year_level }}</td>
                            <td data-label="Semester">{{ $subject->semester }}</td>
                            <td class="numeric-cell" data-label="Units">{{ $subject->units }}</td>
                            <td data-label="Status"><span class="status-badge {{ $subject->is_active ? 'status-approved' : 'status-withdrawn' }}">{{ $subject->is_active ? 'Active' : 'Archived' }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="empty-cell" colspan="6">No subjects are listed.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-row">{{ $subjects->links() }}</div>
    </section>

    {{-- Subject Edit & Archive Modal Dialog --}}
    <dialog id="subject-edit-modal" class="portal-modal" aria-labelledby="subject-modal-title">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-header-copy">
                    <span class="modal-kicker">Curriculum Subject</span>
                    <h2 class="modal-title" id="subject-modal-title">Subject code</h2>
                    <p class="modal-subtitle" id="subject-modal-subtitle">Subject title</p>
                </div>
                <button type="button" class="modal-close-btn" id="subject-modal-close" aria-label="Close dialog">
                    <x-portal-icon name="close" />
                </button>
            </div>

            <form id="subject-edit-form" method="post" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="subject-modal-grid">
                        <div class="field-item">
                            <label class="field-label" for="subject-modal-code">Subject code</label>
                            <input class="form-control" id="subject-modal-code" name="code" maxlength="40" required>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="subject-modal-units">Units</label>
                            <input class="form-control" id="subject-modal-units" name="units" type="number" min="1" max="9" step="0.5" required>
                        </div>

                        <div class="field-item field-item-full">
                            <label class="field-label" for="subject-modal-title-input">Subject title</label>
                            <input class="form-control" id="subject-modal-title-input" name="title" required>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="subject-modal-program">Program</label>
                            <select class="form-control" id="subject-modal-program" name="program_id" required>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->code }} · {{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="subject-modal-major">Major</label>
                            <select class="form-control" id="subject-modal-major" name="major_id">
                                <option value="">No major</option>
                                @foreach ($majors as $major)
                                    <option value="{{ $major->id }}">{{ $major->program_code }} · {{ $major->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="subject-modal-year">Year level</label>
                            <select class="form-control" id="subject-modal-year" name="year_level">
                                @foreach (range(1, 6) as $year)
                                    <option value="{{ $year }}">Year {{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="subject-modal-semester">Semester</label>
                            <select class="form-control" id="subject-modal-semester" name="semester">
                                <option value="1st">1st semester</option>
                                <option value="2nd">2nd semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="justify-content: space-between;">
                    <div>
                        <button type="button" class="button button-quiet button-small" id="subject-toggle-btn">Archive subject</button>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="button button-quiet button-small" id="subject-modal-cancel">Cancel</button>
                        <button type="submit" class="button button-primary button-small">Save changes</button>
                    </div>
                </div>
            </form>

            <form id="subject-toggle-form" method="post" action="" style="display: none;">
                @csrf
                @method('PATCH')
            </form>
        </div>
    </dialog>
    <dialog id="add-subject-modal" class="portal-modal catalog-create-modal" aria-labelledby="add-subject-title">
        <div class="modal-card">
            <div class="modal-header">
                <h2 class="modal-title" id="add-subject-title">Add a subject</h2>
                <button type="button" class="modal-close-btn" data-create-modal-close aria-label="Close dialog"><x-portal-icon name="close" /></button>
            </div>
            <form method="post" action="{{ route('registrar.subjects.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="subject-modal-grid">
                        <div class="field-item">
                            <label class="field-label" for="new-subject-code">Subject code</label>
                            <input class="form-control" id="new-subject-code" name="code" placeholder="IT101" maxlength="40" required>
                        </div>
                        <div class="field-item">
                            <label class="field-label" for="new-subject-units">Units</label>
                            <input class="form-control" id="new-subject-units" name="units" type="number" min="1" max="9" step="0.5" value="3" required>
                        </div>
                        <div class="field-item field-item-full">
                            <label class="field-label" for="new-subject-title">Subject title</label>
                            <input class="form-control" id="new-subject-title" name="title" required>
                        </div>
                        <div class="field-item">
                            <label class="field-label" for="new-subject-program">Program</label>
                            <select class="form-control" id="new-subject-program" name="program_id" required>
                            <option value="">Choose a program</option>
                            @foreach ($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->code }} · {{ $program->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="field-item">
                            <label class="field-label" for="new-subject-major">Major</label>
                            <select class="form-control" id="new-subject-major" name="major_id">
                            <option value="">No major</option>
                            @foreach ($majors as $major)
                            <option value="{{ $major->id }}">{{ $major->program_code }} · {{ $major->name }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="field-item">
                            <label class="field-label" for="new-subject-year">Year level</label>
                            <select class="form-control" id="new-subject-year" name="year_level">
                            @foreach (range(1, 6) as $year)
                            <option value="{{ $year }}">Year {{ $year }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="field-item">
                            <label class="field-label" for="new-subject-semester">Semester</label>
                            <select class="form-control" id="new-subject-semester" name="semester">
                            <option value="1st">1st semester</option>
                            <option value="2nd">2nd semester</option>
                            <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button button-quiet button-small" data-create-modal-close>Cancel</button>
                    <button type="submit" class="button button-primary button-small">Save subject</button>
                </div>
            </form>
        </div>
    </dialog>

@endsection
