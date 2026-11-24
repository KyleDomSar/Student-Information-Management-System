@extends('layouts.portal')

@section('title', 'Audit Logs')
@section('topbar-title', 'Security audit logs')

@section('content')
<div class="mb-4">
    <div class="eyebrow mb-1">SECURITY & ACCOUNTABILITY</div>
    <h1 class="page-title mb-1">Audit Logs</h1>
    <p class="muted mb-0">Review recorded authentication and academic record changes. This page is Admin-only.</p>
</div>

<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-2">
        <div class="col-md">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search action, account, target, or description">
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
        @if($search !== '')<div class="col-auto d-grid"><a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light">Clear</a></div>@endif
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom"><h2 class="h5 fw-bold mb-1">Recorded activity</h2><p class="muted small mb-0">{{ $logs->total() }} event(s) found</p></div>
    @if($logs->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-clock-history"></i></div>
            <h2 class="h6 fw-bold">No audit events found</h2>
            <p class="muted small mb-0">Authentication and record changes will be shown here when recorded.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Date / Time</th><th>Action</th><th>Actor</th><th>Target</th><th>Description</th></tr></thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="small text-nowrap">{{ $log->created_at?->format('M d, Y h:i A') }}</td>
                        <td><span class="badge badge-soft">{{ $log->action }}</span></td>
                        <td><div class="fw-semibold">{{ $log->user?->name ?? 'Guest / System' }}</div><div class="small muted">{{ $log->user?->email ?? '' }}</div></td>
                        <td class="small">{{ $log->target_type ? $log->target_type.' #'.$log->target_id : '—' }}</td>
                        <td class="small muted">{{ $log->description ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
