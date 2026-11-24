@php
    $section = $section ?? null;
    $isEdit = $isEdit ?? false;
@endphp
@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="surface p-3 p-md-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="program_id" class="form-label small fw-semibold">Program / Course *</label>
            <select id="program_id" name="program_id" class="form-select" required>
                <option value="">Select program</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected((string) old('program_id', $section?->program_id ?? '') === (string) $program->id)>{{ $program->code }} · {{ $program->name }}</option>
                @endforeach
            </select>
            @error('program_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="year_level" class="form-label small fw-semibold">Year level *</label>
            <select id="year_level" name="year_level" class="form-select" required>
                <option value="">Select year level</option>
                @foreach($yearLevels as $yearLevel)
                    <option value="{{ $yearLevel->level_number }}" @selected((string) old('year_level', $section?->year_level ?? '') === (string) $yearLevel->level_number)>{{ $yearLevel->name }}</option>
                @endforeach
            </select>
            @error('year_level')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="code" class="form-label small fw-semibold">Section name / code *</label>
            <input id="code" name="code" value="{{ old('code', $section?->code ?? '') }}" class="form-control" maxlength="30" placeholder="e.g. 1A" required>
            @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        @if($isEdit)
            <div class="col-md-6">
                <label for="is_active" class="form-label small fw-semibold">Status *</label>
                <select id="is_active" name="is_active" class="form-select" required>
                    <option value="1" @selected((string) old('is_active', $section->is_active ? '1' : '0') === '1')>Active</option>
                    <option value="0" @selected((string) old('is_active', $section->is_active ? '1' : '0') === '0')>Inactive</option>
                </select>
            </div>
        @endif
    </div>
    <div class="form-text mt-3">Section codes must be unique within the same program and year level.</div>
</div>
<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Save changes' : 'Create section' }}</button>
    <a href="{{ route('admin.sections.index') }}" class="btn btn-light">Cancel</a>
</div>
