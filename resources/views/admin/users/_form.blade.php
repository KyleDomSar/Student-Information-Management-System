@php
    $user = $user ?? null;
    $isEdit = $isEdit ?? false;
@endphp

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="surface p-3 p-md-4">
    <h2 class="h6 fw-bold mb-3">Account details</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label small fw-semibold">Full name *</label>
            <input id="name" name="name" value="{{ old('name', $user?->name ?? '') }}" class="form-control" maxlength="255" required>
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label small fw-semibold">Email address *</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user?->email ?? '') }}" class="form-control" maxlength="255" required>
            @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="password" class="form-label small fw-semibold">{{ $isEdit ? 'New password (optional)' : 'Temporary password *' }}</label>
            <input id="password" type="password" name="password" class="form-control" minlength="10" autocomplete="new-password" {{ $isEdit ? '' : 'required' }}>
            @if($isEdit)<div class="form-text">Leave blank to keep the current password.</div>@endif
            @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="password_confirmation" class="form-label small fw-semibold">Confirm {{ $isEdit ? 'new ' : '' }}password {{ $isEdit ? '' : '*' }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="10" autocomplete="new-password" {{ $isEdit ? '' : 'required' }}>
        </div>
        <div class="col-md-6">
            <label for="role" class="form-label small fw-semibold">Role *</label>
            <select id="role" name="role" class="form-select" required>
                <option value="admin" @selected(old('role', $user?->role ?? 'staff') === 'admin')>Administrator</option>
                <option value="staff" @selected(old('role', $user?->role ?? 'staff') === 'staff')>Staff / Registrar</option>
            </select>
            @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="is_active" class="form-label small fw-semibold">Account status *</label>
            <select id="is_active" name="is_active" class="form-select" required>
                <option value="1" @selected((string) old('is_active', $user ? ($user->is_active ? '1' : '0') : '1') === '1')>Active, can sign in</option>
                <option value="0" @selected((string) old('is_active', $user ? ($user->is_active ? '1' : '0') : '1') === '0')>Inactive, sign-in blocked</option>
            </select>
            @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="alert alert-info border-0 small mt-3"><i class="bi bi-shield-lock me-1"></i> Student accounts are managed separately in Student Records. The last active administrator cannot be demoted or deactivated.</div>
<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Save changes' : 'Create account' }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
</div>
