@php
    $yearLevel = $yearLevel ?? null;
    $isEdit = $isEdit ?? false;
@endphp

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="surface p-3 p-md-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="level_number" class="form-label small fw-semibold">Year level number *</label>
            <input id="level_number" type="number" name="level_number" value="{{ old('level_number', $yearLevel?->level_number ?? '') }}" class="form-control" min="1" max="255" required>
            @error('level_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label for="name" class="form-label small fw-semibold">Display name *</label>
            <input id="name" name="name" value="{{ old('name', $yearLevel?->name ?? '') }}" class="form-control" maxlength="50" placeholder="e.g. First Year" required>
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        @if($isEdit)
            <div class="col-md-4">
                <label for="is_active" class="form-label small fw-semibold">Status *</label>
                <select id="is_active" name="is_active" class="form-select" required>
                    <option value="1" @selected((string) old('is_active', $yearLevel->is_active ? '1' : '0') === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', $yearLevel->is_active ? '1' : '0') === '0')>Inactive</option>
                </select>
            </div>
        @endif
    </div>
    <div class="form-text mt-3">A year level number cannot be changed while student records or sections use that level.</div>
</div>
<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Save changes' : 'Create year level' }}</button>
    <a href="{{ route('admin.year-levels.index') }}" class="btn btn-light">Cancel</a>
</div>
