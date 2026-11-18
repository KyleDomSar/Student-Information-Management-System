@extends('layouts.portal')

@section('title', 'Admin Dashboard')
@section('topbar-title', 'Overview')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">SYSTEM OVERVIEW</div>
        <h1 class="page-title mb-1">Dashboard</h1>
        <p class="muted mb-0">A quick overview of student and academic records.</p>
    </div>
    <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="bi bi-shield-check me-1"></i> {{ ucfirst(auth()->user()->role) }} access</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="surface stat-card">
            <div class="stat-icon mb-3"><i class="bi bi-people"></i></div>
            <div class="muted small fw-semibold">Total students</div>
            <div class="stat-value">{{ number_format($stats['students']) }}</div>
            <div class="small muted">Student profiles</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="surface stat-card">
            <div class="stat-icon mb-3" style="background:#e8f7f2;color:#138b78"><i class="bi bi-person-badge"></i></div>
            <div class="muted small fw-semibold">Staff accounts</div>
            <div class="stat-value">{{ number_format($stats['staff']) }}</div>
            <div class="small muted">Admin and staff</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="surface stat-card">
            <div class="stat-icon mb-3" style="background:#fff4e5;color:#b76a0b"><i class="bi bi-journal-check"></i></div>
            <div class="muted small fw-semibold">Enrollments</div>
            <div class="stat-value">{{ number_format($stats['enrollments']) }}</div>
            <div class="small muted">All recorded terms</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="surface stat-card">
            <div class="stat-icon mb-3" style="background:#f3eefe;color:#8055c8"><i class="bi bi-hourglass-split"></i></div>
            <div class="muted small fw-semibold">Pending approvals</div>
            <div class="stat-value">{{ number_format($stats['pending_enrollments']) }}</div>
            <div class="small muted">Awaiting staff action</div>
        </div>
    </div>
</div>

@if($canViewAuditLogs)
<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h5 fw-bold mb-1">Recent activity</h2>
            <p class="muted small mb-0">Latest recorded authentication and system events.</p>
        </div>
        <span class="badge badge-soft rounded-pill">Audit log</span>
    </div>

    @if($recentLogs->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-clock-history"></i></div>
            <div class="fw-semibold">No activity recorded yet</div>
            <p class="muted small mb-0">System activity will appear here as users begin using SIMS.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr><th>Activity</th><th>User</th><th>Description</th><th class="text-end">Date</th></tr>
                </thead>
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
@else
<div class="surface p-4">
    <h2 class="h6 fw-bold mb-1">Administrative activity</h2>
    <p class="muted small mb-0">Audit log access is restricted to administrators.</p>
</div>
@endif
@endsection
