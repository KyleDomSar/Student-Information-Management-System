@extends('layouts.portal')

@section('title', 'My Grades')
@section('topbar-title', 'My grades')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">ACADEMIC RECORDS</div>
    <h1 class="page-title mb-1">My Grades</h1>
    <p class="muted mb-0">View final grades recorded for your approved enrollments.</p>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom">
        <h2 class="h5 fw-bold mb-1">Final grades</h2>
        <p class="muted small mb-0">Read-only student view. Unencoded grades are shown as pending.</p>
    </div>
    @if($grades->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-card-list"></i></div>
            <h2 class="h6 fw-bold">No grades available yet</h2>
            <p class="muted small mb-0">Final grades will appear here after your enrollment is approved and grades are encoded.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Subject</th><th>Academic year</th><th>Semester</th><th class="text-end">Units</th><th class="text-end">Final grade</th><th>Remarks</th></tr></thead>
                <tbody>
                @foreach($grades as $item)
                    <tr>
                        <td><div class="fw-semibold">{{ $item->subject->code }}</div><div class="small muted">{{ $item->subject->title }}</div></td>
                        <td>{{ $item->enrollment->academic_year }}</td>
                        <td>{{ $item->enrollment->semester }}</td>
                        <td class="text-end">{{ $item->subject->units }}</td>
                        <td class="text-end fw-semibold">{{ $item->final_grade !== null ? number_format((float) $item->final_grade, 2) : 'Pending' }}</td>
                        <td class="small muted">{{ $item->remarks ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="alert alert-info border-0 small mt-3"><i class="bi bi-info-circle me-1"></i> This page is read-only. Contact the registrar if a grade needs to be reviewed or corrected.</div>
@endsection
