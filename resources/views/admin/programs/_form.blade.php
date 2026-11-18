@php
    $program = $program ?? null;
@endphp

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="surface p-3 p-md-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="code" class="form-label small fw-semibold">Program code *</label>
            <input id="code" name="code" value="{{ old('code', $program?->code ?? '') }}" class="form-control" maxlength="30" required>
            @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label small fw-semibold">Program name *</label>
            <input id="name" name="name" value="{{ old('name', $program?->name ?? '') }}" class="form-control" maxlength="255" required>
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        @if($program)
            <div class="col-md-4">
                <label for="is_active" class="form-label small fw-semibold">Program status *</label>
                <select id="is_active" name="is_active" class="form-select" required>
                    <option value="1" @selected((string) old('is_active', $program->is_active ? '1' : '0') === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', $program->is_active ? '1' : '0') === '0')>Inactive</option>
                </select>
            </div>
        @endif
        <div class="col-12">
            <label for="description" class="form-label small fw-semibold">Description</label>
            <textarea id="description" name="description" rows="4" maxlength="2000" class="form-control">{{ old('description', $program?->description ?? '') }}</textarea>
            @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $program ? 'Save changes' : 'Create program' }}</button>
    <a href="{{ route('admin.programs.index') }}" class="btn btn-light">Cancel</a>
</div>
