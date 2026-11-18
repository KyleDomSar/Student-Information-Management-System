@php
    $subject = $subject ?? null;
@endphp

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="surface p-3 p-md-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="code" class="form-label small fw-semibold">Subject code *</label>
            <input id="code" name="code" value="{{ old('code', $subject?->code ?? '') }}" class="form-control" maxlength="40" required>
            @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label for="title" class="form-label small fw-semibold">Subject title *</label>
            <input id="title" name="title" value="{{ old('title', $subject?->title ?? '') }}" class="form-control" maxlength="255" required>
            @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="program_id" class="form-label small fw-semibold">Program</label>
            <select id="program_id" name="program_id" class="form-select">
                <option value="">General / not program-specific</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected((string) old('program_id', $subject?->program_id ?? '') === (string) $program->id)>{{ $program->code }} · {{ $program->name }}</option>
                @endforeach
            </select>
            @error('program_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label for="units" class="form-label small fw-semibold">Units *</label>
            <input id="units" type="number" name="units" min="1" max="12" value="{{ old('units', $subject?->units ?? 3) }}" class="form-control" required>
            @error('units')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        @if($subject)
            <div class="col-md-3">
                <label for="is_active" class="form-label small fw-semibold">Status *</label>
                <select id="is_active" name="is_active" class="form-select" required>
                    <option value="1" @selected((string) old('is_active', $subject->is_active ? '1' : '0') === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', $subject->is_active ? '1' : '0') === '0')>Inactive</option>
                </select>
            </div>
        @endif
    </div>
</div>
<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $subject ? 'Save changes' : 'Create subject' }}</button>
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-light">Cancel</a>
</div>
