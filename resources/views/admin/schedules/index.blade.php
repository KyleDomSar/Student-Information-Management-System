@extends('layouts.portal')

@section('title', 'Class Schedules')
@section('topbar-title', 'Class schedules')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">ACADEMIC SETUP</div>
        <h1 class="page-title mb-1">Class Schedules</h1>
        <p class="muted mb-0">Assign meeting days, times, and rooms to approved enrollment subjects.</p>
    </div>
    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add schedule</a>
</div>

<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.schedules.index') }}" class="row g-2">
        <div class="col-md">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search student, subject, day, or room">
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
        @if($search !== '')<div class="col-auto d-grid"><a href="{{ route('admin.schedules.index') }}" class="btn btn-light">Clear</a></div>@endif
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom">
        <h2 class="h5 fw-bold mb-1">Schedule directory</h2>
        <p class="muted small mb-0">{{ $schedules->total() }} schedule meeting(s) found</p>
    </div>
    @if($schedules->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-calendar-week"></i></div>
            <h2 class="h6 fw-bold">No class schedules found</h2>
            <p class="muted small mb-3">Add a schedule for a subject in an approved enrollment.</p>
            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary btn-sm">Add schedule</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Day</th><th>Time</th><th>Subject</th><th>Student</th><th>Room</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach($schedules as $schedule)
                    <tr>
                        <td class="fw-semibold">{{ $schedule->day_of_week }}</td>
                        <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('h:i A') }} to {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                        <td><div class="fw-semibold">{{ $schedule->enrollmentSubject->subject->code }}</div><div class="small muted">{{ $schedule->enrollmentSubject->subject->title }}</div></td>
                        <td><div>{{ $schedule->enrollmentSubject->enrollment->student->user->name }}</div><div class="small muted">{{ $schedule->enrollmentSubject->enrollment->student->student_number }}</div></td>
                        <td>{{ $schedule->room ?: 'TBA' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="d-inline" onsubmit="return confirm('Delete this class schedule?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $schedules->links() }}</div>
    @endif
</div>
@endsection
