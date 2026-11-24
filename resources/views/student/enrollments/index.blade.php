@extends('layouts.portal')

@section('title', 'My Enrollments')
@section('topbar-title', 'My enrollment history')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">ACADEMIC RECORDS</div>
    <h1 class="page-title mb-1">My Enrollments</h1>
    <p class="muted mb-0">View the enrollment records associated with your student account.</p>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom">
        <h2 class="h5 fw-bold mb-1">Enrollment history</h2>
        <p class="muted small mb-0">This page is read-only. Contact the registrar for corrections.</p>
    </div>
    @if($enrollments->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-journal-x"></i></div>
            <h2 class="h6 fw-bold">No enrollment records available</h2>
            <p class="muted small mb-0">Your enrollment history will appear here once records are created by the registrar.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Academic year</th><th>Semester</th><th>Subjects</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @foreach($enrollments as $enrollment)
                    <tr>
                        <td class="fw-semibold">{{ $enrollment->academic_year }}</td>
                        <td>{{ $enrollment->semester }}</td>
                        <td>{{ $enrollment->enrollment_subjects_count }}</td>
                        <td>
                            @php($statusClass = ['pending' => 'text-bg-warning', 'enrolled' => 'text-bg-success', 'rejected' => 'text-bg-danger'][$enrollment->status] ?? 'text-bg-secondary')
                            <span class="badge {{ $statusClass }} text-capitalize">{{ $enrollment->status }}</span>
                        </td>
                        <td class="text-end"><a href="{{ route('student.enrollments.show', $enrollment) }}" class="btn btn-sm btn-outline-primary">View details</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($enrollments, 'links'))
            <div class="p-3">{{ $enrollments->links() }}</div>
        @endif
    @endif
</div>
@endsection
