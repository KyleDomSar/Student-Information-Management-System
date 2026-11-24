@php
    $program = $program ?? null;
    $checkedYearLevels = old('year_levels', $selectedYearLevels ?? []);
    $checkedYearLevels = array_map('intval', (array) $checkedYearLevels);
@endphp

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="surface p-3 p-md-4">
    <h2 class="h6 fw-bold mb-3">Program details</h2>
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
        <div class="col-md-6">
            <label for="college_id" class="form-label small fw-semibold">College *</label>
            <select id="college_id" name="college_id" class="form-select" required>
                <option value="">Select college</option>
                @foreach($colleges as $college)
                    <option value="{{ $college->id }}" @selected((string) old('college_id', $program?->college_id ?? '') === (string) $college->id)>{{ $college->name }}{{ $college->campus ? ' · '.$college->campus : '' }}</option>
                @endforeach
            </select>
            @error('college_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="department_id" class="form-label small fw-semibold">Department</label>
            <select id="department_id" name="department_id" class="form-select">
                <option value="">No department assigned</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" data-college="{{ $department->college_id }}" @selected((string) old('department_id', $program?->department_id ?? '') === (string) $department->id)>{{ $department->code }} · {{ $department->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Choose only when the program's department assignment is confirmed.</div>
            @error('department_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
            <textarea id="description" name="description" rows="3" maxlength="2000" class="form-control">{{ old('description', $program?->description ?? '') }}</textarea>
            @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="surface p-3 p-md-4 mt-3">
    <h2 class="h6 fw-bold mb-2">Available year levels *</h2>
    <p class="muted small mb-3">Select the year levels supported by this program's approved curriculum. Do not assume every program has the same duration.</p>
    <div class="row g-2">
        @forelse($yearLevels as $yearLevel)
            <div class="col-sm-6 col-lg-4">
                <label class="form-check border rounded-3 p-3 ps-5 h-100">
                    <input class="form-check-input" type="checkbox" name="year_levels[]" value="{{ $yearLevel->level_number }}" @checked(in_array($yearLevel->level_number, $checkedYearLevels, true))>
                    <span class="form-check-label fw-semibold">{{ $yearLevel->name }}</span>
                </label>
            </div>
        @empty
            <div class="col-12"><p class="small text-danger mb-0">Create at least one active year level before adding a program.</p></div>
        @endforelse
    </div>
    @error('year_levels')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div>

<div class="surface p-3 p-md-4 mt-3">
    <h2 class="h6 fw-bold mb-2">Majors / specializations</h2>
    <p class="muted small mb-3">Optional. Enter one official major or specialization per line. Existing majors are preserved when editing.</p>
    <label for="majors" class="form-label small fw-semibold">Major names</label>
    <textarea id="majors" name="majors" rows="4" maxlength="5000" class="form-control" placeholder="Animal Science&#10;Crop Science">{{ old('majors', $majorText ?? '') }}</textarea>
    @error('majors')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $program ? 'Save changes' : 'Create program' }}</button>
    <a href="{{ route('admin.programs.index') }}" class="btn btn-light">Cancel</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const college = document.getElementById('college_id');
    const department = document.getElementById('department_id');
    if (!college || !department) return;

    const filterDepartments = () => {
        const collegeId = college.value;
        Array.from(department.options).forEach((option) => {
            if (!option.value) return;
            option.hidden = option.dataset.college !== collegeId;
            option.disabled = option.hidden;
        });
        const selected = department.selectedOptions[0];
        if (selected && selected.value && selected.dataset.college !== collegeId) department.value = '';
    };

    college.addEventListener('change', filterDepartments);
    filterDepartments();
});
</script>
