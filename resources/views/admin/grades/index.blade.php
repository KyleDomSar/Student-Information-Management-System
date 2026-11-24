@extends('layouts.portal')

@section('title', 'Gradebook')
@section('topbar-title', 'Gradebook')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">ACADEMIC RECORDS</div>
        <h1 class="page-title mb-1">Gradebook</h1>
        <p class="muted mb-0">Encode and update final grades for approved enrollments.</p>
    </div>
    <span class="badge badge-soft rounded-pill px-3 py-2">Admin / Staff only</span>
</div>

<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.grades.index') }}" class="row g-2">
        <div class="col-md">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search student, student number, or subject">
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
        @if($search !== '')<div class="col-auto d-grid"><a href="{{ route('admin.grades.index') }}" class="btn btn-light">Clear</a></div>@endif
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom">
        <h2 class="h5 fw-bold mb-1">Approved enrollment subjects</h2>
        <p class="muted small mb-0">{{ $grades->total() }} subject record(s) found. Pending and rejected enrollments are excluded.</p>
    </div>
    @if($grades->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-card-checklist"></i></div>
            <h2 class="h6 fw-bold">No grade records found</h2>
            <p class="muted small mb-0">Approved enrollment subjects will appear here for grade encoding.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Student</th><th>Subject</th><th>Term</th><th>Final grade</th><th>Remarks</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @foreach($grades as $item)
                    <tr>
                        <td><div class="fw-semibold">{{ $item->enrollment->student->user->name }}</div><div class="small muted">{{ $item->enrollment->student->student_number }}</div></td>
                        <td><div class="fw-semibold">{{ $item->subject->code }}</div><div class="small muted">{{ $item->subject->title }}</div></td>
                        <td><div>{{ $item->enrollment->academic_year }}</div><div class="small muted">{{ $item->enrollment->semester }}</div></td>
                        <td class="fw-semibold">{{ $item->final_grade !== null ? number_format((float) $item->final_grade, 2) : 'Not encoded' }}</td>
                        <td class="small muted">{{ $item->remarks ?: '—' }}</td>
                        <td class="text-end"><a href="{{ route('admin.grades.edit', $item) }}" class="btn btn-sm btn-outline-primary">{{ $item->final_grade === null ? 'Encode grade' : 'Edit grade' }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $grades->links() }}</div>
    @endif
</div>
@endsection
