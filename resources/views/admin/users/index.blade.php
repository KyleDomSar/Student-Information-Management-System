@extends('layouts.portal')

@section('title', 'User Management')
@section('topbar-title', 'User management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-1">SECURITY & ACCESS</div>
        <h1 class="page-title mb-1">User Management</h1>
        <p class="muted mb-0">Manage administrator and staff accounts. Student accounts are managed through Student Records.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add account</a>
</div>

<div class="surface p-3 mb-3">
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2">
        <div class="col-md">
            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name or email">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                <option value="admin" @selected($role === 'admin')>Administrator</option>
                <option value="staff" @selected($role === 'staff')>Staff / Registrar</option>
            </select>
        </div>
        <div class="col-auto d-grid"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Filter</button></div>
        @if($search !== '' || $role)<div class="col-auto d-grid"><a href="{{ route('admin.users.index') }}" class="btn btn-light">Clear</a></div>@endif
    </form>
</div>

<div class="surface overflow-hidden">
    <div class="p-3 p-md-4 border-bottom"><h2 class="h5 fw-bold mb-1">Administrator and staff accounts</h2><p class="muted small mb-0">{{ $users->total() }} account(s) found</p></div>
    @if($users->isEmpty())
        <div class="text-center p-5">
            <div class="stat-icon mx-auto mb-3"><i class="bi bi-people"></i></div>
            <h2 class="h6 fw-bold">No accounts found</h2>
            <p class="muted small mb-0">Create an administrator or staff account to get started.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Account</th><th>Role</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><div class="fw-semibold">{{ $user->name }}</div><div class="small muted">{{ $user->email }}</div></td>
                        <td><span class="badge badge-soft text-capitalize">{{ $user->role }}</span></td>
                        <td><span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="small muted">{{ $user->created_at?->format('M d, Y') }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if($user->is_active && $user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Deactivate this account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                </form>
                            @endif
                            @if($user->id === auth()->id())<span class="badge text-bg-light">Current account</span>@endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $users->links() }}</div>
    @endif
</div>
@endsection
