@extends('layouts.app')

@section('title', 'Subject catalog')
@section('eyebrow', 'Student portal / Subjects')

@section('content')
    <section class="panel list-panel">
        @if ($subjects->isNotEmpty())
            <div class="table-wrap"><table class="responsive-table"><thead><tr><th scope="col">Subject</th><th scope="col">Program</th><th scope="col">Year</th><th scope="col">Semester</th><th scope="col" class="numeric-cell">Units</th></tr></thead><tbody>
                @foreach ($subjects as $subject)
                    <tr>
                        <td data-label="Subject"><strong>{{ $subject->code }}</strong><small>{{ $subject->title }}</small></td>
                        <td data-label="Program"><strong>{{ $subject->program?->code ?? '' }}</strong></td>
                        <td data-label="Year">{{ $subject->year_level }}{{ $subject->year_level === 1 ? 'st' : ($subject->year_level === 2 ? 'nd' : ($subject->year_level === 3 ? 'rd' : 'th')) }}</td>
                        <td data-label="Semester">{{ $subject->semester }}</td>
                        <td class="numeric-cell" data-label="Units">{{ $subject->units }}</td>
                    </tr>
                @endforeach
            </tbody></table></div>
            <div class="pagination-row">{{ $subjects->links() }}</div>
        @else
            <div class="empty-state"><span class="empty-mark" aria-hidden="true">▧</span><h2>The catalog is being prepared.</h2><p>Active subjects will appear as soon as the academic catalog is published.</p></div>
        @endif
    </section>
@endsection
