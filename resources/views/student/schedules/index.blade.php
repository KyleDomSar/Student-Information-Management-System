@extends('layouts.portal')

@section('title', 'My Class Schedule')
@section('topbar-title', 'My class schedule')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">ACADEMIC RECORDS</div>
    <h1 class="page-title mb-1">My Class Schedule</h1>
    <p class="muted mb-0">Your timetable for approved enrollment records.</p>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom">
        <h2 class="h5 fw-bold mb-1">Weekly class meetings</h2>
        <p class="muted small mb-0">Read-only schedule maintained by authorized school personnel.</p>
    </div>
    @if($schedules->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-calendar-week"></i></div>
            <h2 class="h6 fw-bold">No class schedules available</h2>
            <p class="muted small mb-0">Your schedule will appear here once the registrar assigns class meetings to your approved subjects.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Day</th><th>Time</th><th>Subject</th><th>Room</th><th>Academic year</th><th>Semester</th></tr></thead>
                <tbody>
                @foreach($schedules as $schedule)
                    <tr>
                        <td class="fw-semibold">{{ $schedule->day_of_week }}</td>
                        <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('h:i A') }} to {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                        <td><div class="fw-semibold">{{ $schedule->enrollmentSubject->subject->code }}</div><div class="small muted">{{ $schedule->enrollmentSubject->subject->title }}</div></td>
                        <td>{{ $schedule->room ?: 'TBA' }}</td>
                        <td>{{ $schedule->enrollmentSubject->enrollment->academic_year }}</td>
                        <td>{{ $schedule->enrollmentSubject->enrollment->semester }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="alert alert-info border-0 small mt-3"><i class="bi bi-info-circle me-1"></i> This schedule is read-only. Contact the registrar if any class meeting details need correction.</div>
@endsection
