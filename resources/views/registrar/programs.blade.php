@extends('layouts.app')

@section('title', 'Program catalog')
@section('eyebrow', 'Registrar / Programs')

@section('content')
    <section class="panel list-panel">
        <div class="catalog-table-toolbar">
            <h2 class="panel-heading-title catalog-table-title" id="programs-table-title">Programs</h2>
            <button type="button" class="button button-primary catalog-add-button" data-create-modal="add-program-modal" aria-haspopup="dialog" aria-controls="add-program-modal">Add a program <span aria-hidden="true">&#43;</span></button>
        </div>
        <div class="table-wrap">
            <table class="responsive-table programs-table" aria-labelledby="programs-table-title">
                <thead>
                    <tr>
                        <th scope="col">Program</th>
                        <th scope="col">Department</th>
                        <th scope="col" class="numeric-cell">Length</th>
                        <th scope="col" class="numeric-cell">Subjects</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programs as $program)
                        <tr class="clickable-program-row"
                            tabindex="0"
                            role="button"
                            aria-label="Manage program {{ $program->code }} - {{ $program->name }}"
                            data-action="{{ route('registrar.programs.update', $program) }}"
                            data-toggle-action="{{ route('registrar.programs.toggle', $program) }}"
                            data-code="{{ $program->code }}"
                            data-name="{{ $program->name }}"
                            data-department="{{ $program->department }}"
                            data-duration="{{ $program->duration_years }}"
                            data-is-active="{{ $program->is_active ? '1' : '0' }}">
                            <td data-label="Program">
                                <strong>{{ $program->code }}</strong>
                                <small>{{ $program->name }}</small>
                            </td>
                            <td data-label="Department">{{ $program->department }}</td>
                            <td class="numeric-cell" data-label="Length">{{ $program->duration_years }} years</td>
                            <td class="numeric-cell" data-label="Subjects">{{ $program->subjects_count }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty-cell" colspan="4">No programs are listed.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-row">{{ $programs->links() }}</div>
    </section>

    {{-- Program Edit & Archive Modal Dialog --}}
    <dialog id="program-edit-modal" class="portal-modal" aria-labelledby="program-modal-title">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-header-copy">
                    <span class="modal-kicker">Academic Program</span>
                    <h2 class="modal-title" id="program-modal-title">Program Code</h2>
                    <p class="modal-subtitle" id="program-modal-subtitle">Program Name</p>
                </div>
                <button type="button" class="modal-close-btn" id="program-modal-close" aria-label="Close dialog">
                    <x-portal-icon name="close" />
                </button>
            </div>

            <form id="program-edit-form" method="post" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="program-modal-grid">
                        <div class="field-item">
                            <label class="field-label" for="program-modal-code">Program code</label>
                            <input class="form-control" id="program-modal-code" name="code" maxlength="20" required>
                        </div>

                        <div class="field-item">
                            <label class="field-label" for="program-modal-duration">Program length</label>
                            <select class="form-control" id="program-modal-duration" name="duration_years">
                                @foreach (range(1, 6) as $years)
                                    <option value="{{ $years }}">{{ $years }} years</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-item field-item-full">
                            <label class="field-label" for="program-modal-name">Program name</label>
                            <input class="form-control" id="program-modal-name" name="name" required>
                        </div>

                        <div class="field-item field-item-full">
                            <label class="field-label" for="program-modal-department">Department</label>
                            <input class="form-control" id="program-modal-department" name="department" maxlength="120" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="justify-content: space-between;">
                    <div>
                        <button type="button" class="button button-quiet button-small" id="program-toggle-btn">Archive program</button>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="button button-quiet button-small" id="program-modal-cancel">Cancel</button>
                        <button type="submit" class="button button-primary button-small">Save changes</button>
                    </div>
                </div>
            </form>

            <form id="program-toggle-form" method="post" action="" style="display: none;">
                @csrf
                @method('PATCH')
            </form>
        </div>
    </dialog>
    <dialog id="add-program-modal" class="portal-modal catalog-create-modal" aria-labelledby="add-program-title">
        <div class="modal-card">
            <div class="modal-header">
                <h2 class="modal-title" id="add-program-title">Add a program</h2>
                <button type="button" class="modal-close-btn" data-create-modal-close aria-label="Close dialog"><x-portal-icon name="close" /></button>
            </div>
            <form method="post" action="{{ route('registrar.programs.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="program-modal-grid">
                        <div class="field-item">
                            <label class="field-label" for="new-code">Program code</label>
                            <input class="form-control" id="new-code" name="code" value="{{ old('code') }}" placeholder="BSIT" maxlength="20" required>
                        </div>
                        <div class="field-item">
                            <label class="field-label" for="new-duration">Program length</label>
                            <select class="form-control" id="new-duration" name="duration_years">
                            @foreach (range(1, 6) as $years)
                            <option value="{{ $years }}">{{ $years }} years</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="field-item field-item-full">
                            <label class="field-label" for="new-name">Program name</label>
                            <input class="form-control" id="new-name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="field-item field-item-full">
                            <label class="field-label" for="new-department">Department</label>
                            <input class="form-control" id="new-department" name="department" value="{{ old('department', 'Buenavista Community College') }}" maxlength="120" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button button-quiet button-small" data-create-modal-close>Cancel</button>
                    <button type="submit" class="button button-primary button-small">Save program</button>
                </div>
            </form>
        </div>
    </dialog>

@endsection
