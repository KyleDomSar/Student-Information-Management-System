@extends('layouts.portal')

@section('title', 'Admin Dashboard')
@section('topbar-title', 'Overview')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">SYSTEM OVERVIEW</div>
    <h1 class="page-title mb-1">Dashboard</h1>
    <p class="muted mb-0">A quick view of the student records maintained in SIMS.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-8 col-md-6 col-xl-4">
        <div class="surface stat-card">
            <div class="stat-icon mb-3"><i class="bi bi-people"></i></div>
            <div class="muted small fw-semibold">Total registered students</div>
            <div class="stat-value">{{ number_format($totalStudents) }}</div>
            <div class="small muted">Student profiles in the system</div>
        </div>
    </div>
</div>

@if($canViewAuditLogs)
    <div class="surface overflow-hidden">
        <div class="p-3 p-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h5 fw-bold mb-1">Recent activity</h2>
                <p class="muted small mb-0">Latest authentication and system events recorded in SIMS.</p>
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-primary">
                View audit logs <i class="bi bi-arrow-up-right ms-1"></i>
            </a>
        </div>
        @if($recentLogs->isEmpty())
            <div class="text-center p-5">
                <div class="stat-icon mx-auto mb-3"><i class="bi bi-clock-history"></i></div>
                <div class="fw-semibold">No activity recorded yet</div>
                <p class="muted small mb-0">Recorded system activity will appear here.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Activity</th><th>User</th><th>Description</th><th class="text-end">Date</th></tr></thead>
                    <tbody>
                    @foreach($recentLogs as $log)
                        <tr>
                            <td><span class="badge text-bg-light">{{ $log->action }}</span></td>
                            <td class="fw-semibold">{{ $log->user?->name ?? 'System / Guest' }}</td>
                            <td class="small muted">{{ $log->description }}</td>
                            <td class="text-end small muted text-nowrap">{{ $log->created_at?->format('M d, Y h:i A') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
@endsection
