# Database Overview

This describes the current initial database design implemented by the Laravel migrations and Eloquent models. Additional fields may be introduced as the project scope evolves.

## 1. Core tables

### users
Stores login credentials and the role assigned to each account.

| Field | Purpose |
|---|---|
| id | Primary key |
| name | Account holder's display name |
| email | Unique login email |
| password | Hashed password |
| role | admin, staff, or student |
| is_active | Whether the account is enabled |
| email_verified_at | Optional verification timestamp |
| created_at / updated_at | Timestamps |

### students
Stores student-specific profile and academic identifiers.

| Field | Purpose |
|---|---|
| id | Primary key |
| user_id | Foreign key to users.id, unique |
| student_number | Unique student number |
| program_id | Foreign key to programs.id |
| section_id | Nullable foreign key to sections.id |
| year_level | Numeric year-level identifier linked to year_levels.level_number |
| status | Active, inactive, graduated, or other approved status |
| created_at / updated_at | Timestamps |

### year_levels
Stores the year levels configured by the Administrator.

| Field | Purpose |
|---|---|
| id | Primary key |
| level_number | Unique numeric year-level identifier |
| name | Display name, such as First Year |
| is_active | Whether the year level can be assigned |
| created_at / updated_at | Timestamps |

### sections
Stores section groups under a program and year level.

| Field | Purpose |
|---|---|
| id | Primary key |
| program_id | Foreign key to programs.id |
| year_level | Foreign key to year_levels.level_number |
| code | Section name or code |
| is_active | Whether the section can be assigned |
| created_at / updated_at | Timestamps |

Section code is unique within a program and year level.

### programs
Stores academic programs or courses.

| Field | Purpose |
|---|---|
| id | Primary key |
| code | Unique program code |
| name | Program name |
| is_active | Whether the program is available |

### subjects
Stores subject catalog information.

| Field | Purpose |
|---|---|
| id | Primary key |
| program_id | Foreign key to programs.id, if program-specific |
| code | Subject code |
| title | Subject title |
| units | Number of units |
| is_active | Whether the subject is available |

### enrollments
Stores a student's enrollment per academic year and semester.

| Field | Purpose |
|---|---|
| id | Primary key |
| student_id | Foreign key to students.id |
| academic_year | Example: 2026-2027 |
| semester | First, second, summer, or approved value |
| status | Pending, enrolled, rejected, or dropped |
| created_by | User ID of the staff member who created the record |
| created_at / updated_at | Timestamps |

### enrollment_subjects
Associates subjects with a particular enrollment and stores the student's grade for that subject.

| Field | Purpose |
|---|---|
| id | Primary key |
| enrollment_id | Foreign key to enrollments.id |
| subject_id | Foreign key to subjects.id |
| instructor_user_id | Optional foreign key to users.id |
| final_grade | Nullable until encoded |
| remarks | Optional grade remarks |
| created_at / updated_at | Timestamps |

### class_schedules
Stores class meeting details for an enrolled subject.

| Field | Purpose |
|---|---|
| id | Primary key |
| enrollment_subject_id | Foreign key to enrollment_subjects.id |
| day_of_week | Scheduled day |
| start_time | Start time |
| end_time | End time |
| room | Room or location |

### audit_logs
Stores important administrative and academic record actions.

| Field | Purpose |
|---|---|
| id | Primary key |
| user_id | Nullable actor user ID |
| action | Action name |
| target_type | Affected model or record type |
| target_id | Affected record ID |
| description | Sanitized summary |
| created_at | Event timestamp |

## 2. Planned requirement-driven tables\n\nYear-level and section management are now represented by the year_levels and sections tables, with students.section_id linking student records to their assigned section. Year levels are configured by the Administrator rather than assuming a fixed institution-wide list. Student visitor time-out recording remains pending until the exact workflow and required data fields are confirmed.\n\n## 3. Relationships

- One user account may have one student profile.
- One program has many students, subjects, and sections.
- One year level may be assigned to many students and sections.
- One section belongs to one program and year level and may be assigned to many students.
- One student may be assigned to one section.
- One student has many enrollment records.
- One enrollment has many enrollment-subject records.
- One subject may appear in many enrollment-subject records.
- One enrollment-subject record may have one or more schedule entries.
- One user may create many audit log entries.

## 4. Relationship sketch

```text
users
  |
  | 1 to 0..1
  v
students -------- many to 1 -------- programs
  |                                  |
  | many to 1                        | 1 to many
  v                                  v
sections <--------- many to 1 ---- year_levels
  |
  | 1 to many
  v
enrollments
  |
  | 1 to many
  v
enrollment_subjects -------- many to 1 -------- subjects
  |                                      |
  | 1 to many                            | many to 1 (optional)
  v                                      v
class_schedules                       programs

users -------- 1 to many -------- audit_logs
```

## 5. Database integrity and privacy rules

- Add unique constraints to user email, student number, program code, and subject code.
- Prevent duplicate enrollment terms for the same student using a unique constraint on student, academic year, and semester.
- Prevent duplicate subject entries within one enrollment using a unique constraint on enrollment and subject.
- Add foreign keys and indexes to relationship columns.
- Use database transactions when creating an enrollment and its subject records.
- Prevent duplicate subject entries within the same enrollment.
- Do not store plaintext passwords.
- Avoid adding personal or financial fields unless the project requirements need them.
- The Student Portal must query records through the authenticated student's relationship, not through unrestricted IDs.
- Final grades are stored as nullable decimal values. The allowed minimum and maximum are configured through `SIMS_GRADE_MIN` and `SIMS_GRADE_MAX` in the local environment.
