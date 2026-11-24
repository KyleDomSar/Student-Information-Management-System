@php
    $schedule = $schedule ?? null;
@endphp

@if($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-1">Please review the form.</div><ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="surface p-3 p-md-4">
    <div class="row g-3">
        <div class="col-12">
            <label for="enrollment_subject_id" class="form-label small fw-semibold">Approved enrollment subject *</label>
            <select id="enrollment_subject_id" name="enrollment_subject_id" class="form-select" required>
                <option value="">Choose an approved enrollment subject</option>
                @foreach($enrollmentSubjects as $item)
                    <option value="{{ $item->id }}" @selected((string) old('enrollment_subject_id', $schedule?->enrollment_subject_id ?? '') === (string) $item->id)>
                        {{ $item->enrollment->student->student_number }} · {{ $item->enrollment->student->user->name }} · {{ $item->subject->code }} · {{ $item->enrollment->academic_year }} {{ $item->enrollment->semester }}
                    </option>
                @endforeach
            </select>
            @error('enrollment_subject_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @if($enrollmentSubjects->isEmpty())<div class="form-text text-danger">There are no subjects from approved enrollments available for scheduling.</div>@endif
        </div>
        <div class="col-md-4">
            <label for="day_of_week" class="form-label small fw-semibold">Day *</label>
            <select id="day_of_week" name="day_of_week" class="form-select" required>
                <option value="">Choose a day</option>
                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    <option value="{{ $day }}" @selected(old('day_of_week', $schedule?->day_of_week ?? '') === $day)>{{ $day }}</option>
                @endforeach
            </select>
            @error('day_of_week')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="start_time" class="form-label small fw-semibold">Start time *</label>
            <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $schedule ? substr($schedule->start_time, 0, 5) : '') }}" class="form-control" required>
            @error('start_time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="end_time" class="form-label small fw-semibold">End time *</label>
            <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $schedule ? substr($schedule->end_time, 0, 5) : '') }}" class="form-control" required>
            @error('end_time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="room" class="form-label small fw-semibold">Room</label>
            <input id="room" name="room" value="{{ old('room', $schedule?->room ?? '') }}" class="form-control" maxlength="100" placeholder="e.g. Laboratory 2">
            @error('room')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary" {{ $enrollmentSubjects->isEmpty() ? 'disabled' : '' }}><i class="bi bi-check2 me-1"></i>{{ $schedule ? 'Save changes' : 'Create schedule' }}</button>
    <a href="{{ route('admin.schedules.index') }}" class="btn btn-light">Cancel</a>
</div>
