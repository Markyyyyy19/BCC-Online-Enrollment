@extends('layouts.app')

@section('title', 'My grades')
@section('eyebrow', 'Student portal / Grades')

@section('content')
    <section class="panel list-panel">
        @if ($grades->isNotEmpty())
            <div class="table-wrap"><table class="responsive-table"><thead><tr><th scope="col">Subject</th><th scope="col">Program</th><th scope="col">Term</th><th scope="col" class="numeric-cell">Midterm</th><th scope="col" class="numeric-cell">Final</th><th scope="col" class="numeric-cell">Average</th><th scope="col">Remarks</th></tr></thead><tbody>
                @foreach ($grades as $grade)
                    <tr>
                        <td data-label="Subject"><strong>{{ $grade->code }}</strong><small>{{ $grade->title }} · {{ $grade->units }} units</small></td>
                        <td data-label="Program">{{ $grade->program_code }}</td>
                        <td class="term-cell" data-label="Term">{{ $grade->school_year }} · {{ $grade->semester }}</td>
                        <td class="numeric-cell" data-label="Midterm">{{ $grade->midterm ?? '—' }}</td>
                        <td class="numeric-cell" data-label="Final">{{ $grade->final_term ?? '—' }}</td>
                        <td class="numeric-cell" data-label="Average"><strong>{{ $grade->final_grade ?? '—' }}</strong></td>
                        <td data-label="Remarks"><span class="status-badge status-{{ strtolower(str_replace(' ', '-', $grade->remarks)) }}">{{ $grade->remarks }}</span></td>
                    </tr>
                @endforeach
            </tbody></table></div>
            @if ($grades->hasPages())<div class="pagination-row">{{ $grades->links() }}</div>@endif
        @else
            <div class="empty-state"><span class="empty-mark" aria-hidden="true">↗</span><h2>No grades have been posted yet.</h2><p>When an instructor records a grade for your enrolled subjects, it will appear here.</p></div>
        @endif
    </section>
@endsection
