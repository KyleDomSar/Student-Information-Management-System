@php
    $student = $student ?? null;
    $isEdit = $isEdit ?? false;
@endphp
@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="surface p-3 p-md-4">
    <h2 class="h6 fw-bold mb-3">Account information</h2>
    <div class="row g-3">
        <div class="col-md-6"><label for="name" class="form-label small fw-semibold">Full name *</label><input id="name" name="name" value="{{ old('name', $student?->user?->name ?? '') }}" class="form-control" maxlength="255" required>@error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label for="email" class="form-label small fw-semibold">Email address *</label><input id="email" type="email" name="email" value="{{ old('email', $student?->user?->email ?? '') }}" class="form-control" maxlength="255" required>@error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        @if(!$isEdit || auth()->user()->isAdministrator())
            <div class="col-md-6"><label for="password" class="form-label small fw-semibold">{{ $isEdit ? 'Reset password (optional)' : 'Temporary password *' }}</label><input id="password" type="password" name="password" class="form-control" minlength="10" autocomplete="new-password" {{ $isEdit ? '' : 'required' }}>@if($isEdit)<div class="form-text">Leave blank to keep the current password. Staff cannot reset an existing student account password.</div>@endif @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label for="password_confirmation" class="form-label small fw-semibold">Confirm {{ $isEdit ? 'new ' : '' }}password {{ $isEdit ? '' : '*' }}</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="10" autocomplete="new-password" {{ $isEdit ? '' : 'required' }}></div>
        @endif
    </div>
</div>
<div class="surface p-3 p-md-4 mt-3">
    <h2 class="h6 fw-bold mb-3">Academic information</h2>
    <div class="row g-3">
        <div class="col-md-6"><label for="student_number" class="form-label small fw-semibold">Student number *</label><input id="student_number" name="student_number" value="{{ old('student_number', $student?->student_number ?? '') }}" class="form-control" maxlength="50" required>@error('student_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label for="program_id" class="form-label small fw-semibold">Program / Course</label><select id="program_id" name="program_id" class="form-select"><option value="">Not assigned</option>@foreach($programs as $program)<option value="{{ $program->id }}" @selected((string) old('program_id', $student?->program_id ?? '') === (string) $program->id)>{{ $program->code }} · {{ $program->name }}</option>@endforeach</select>@error('program_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label for="year_level" class="form-label small fw-semibold">Year level</label><select id="year_level" name="year_level" class="form-select"><option value="">Not assigned</option>@foreach($yearLevels as $yearLevel)<option value="{{ $yearLevel->level_number }}" @selected((string) old('year_level', $student?->year_level ?? '') === (string) $yearLevel->level_number)>{{ $yearLevel->name }}</option>@endforeach</select>@error('year_level')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label for="section_id" class="form-label small fw-semibold">Assigned section</label><select id="section_id" name="section_id" class="form-select"><option value="">Not assigned</option>@foreach($sections as $section)<option value="{{ $section->id }}" data-program="{{ $section->program_id }}" data-year="{{ $section->year_level }}" @selected((string) old('section_id', $student?->section_id ?? '') === (string) $section->id)>{{ $section->program->code }} · {{ $section->yearLevel->name }} · {{ $section->code }} {{ !$section->is_active ? '(Inactive, existing assignment)' : '' }}</option>@endforeach</select><div class="form-text">Choose a section that matches the selected program and year level.</div>@error('section_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label for="status" class="form-label small fw-semibold">Student status *</label><select id="status" name="status" class="form-select" required>@foreach(['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $student?->status ?? 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
        @if(!$isEdit && auth()->user()->isAdministrator())
            <div class="col-md-6"><label for="account_active" class="form-label small fw-semibold">Portal account *</label><select id="account_active" name="account_active" class="form-select" required><option value="1" @selected((string) old('account_active', '1') === '1')>Active, can sign in</option><option value="0" @selected((string) old('account_active', '1') === '0')>Inactive, sign-in blocked</option></select><div class="form-text">Only administrators can activate or deactivate student portal accounts.</div></div>
        @endif
    </div>
</div>
<div class="d-flex flex-wrap gap-2 mt-3"><button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Save changes' : 'Create student' }}</button><a href="{{ $isEdit ? route('admin.students.show', $student) : route('admin.students.index') }}" class="btn btn-light">Cancel</a></div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const program = document.getElementById('program_id');
    const year = document.getElementById('year_level');
    const section = document.getElementById('section_id');
    if (!program || !year || !section) return;
    const filterSections = () => {
        const programId = program.value;
        const yearLevel = year.value;
        Array.from(section.options).forEach((option) => {
            if (!option.value) return;
            const matches = option.dataset.program === programId && option.dataset.year === yearLevel;
            option.hidden = !matches;
            option.disabled = !matches;
        });
        const selected = section.selectedOptions[0];
        if (selected && selected.value && selected.disabled) section.value = '';
    };
    program.addEventListener('change', filterSections);
    year.addEventListener('change', filterSections);
    filterSections();
});
</script>
