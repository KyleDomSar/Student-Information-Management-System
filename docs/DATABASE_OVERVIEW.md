# Database Overview

This describes the current database design implemented by the Laravel migrations and Eloquent models.

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

### colleges
Stores colleges under a campus. The initial reference data includes the 11 CvSU Main Campus colleges.

| Field | Purpose |
|---|---|
| id | Primary key |
| name | Official college name |
| campus | Campus label, initially Main Campus |
| is_active | Whether the college can be assigned |

### departments
Stores departments belonging to a college. The initial reference data includes the five CEIT departments.

| Field | Purpose |
|---|---|
| id | Primary key |
| college_id | Foreign key to colleges.id |
| code | Unique department code |
| name | Department name |
| is_active | Whether the department can be assigned |

### programs
Stores degree programs or courses.

| Field | Purpose |
|---|---|
| id | Primary key |
| college_id | Nullable foreign key to colleges.id |
| department_id | Nullable foreign key to departments.id |
| code | Unique program code |
| name | Program name |
| description | Optional description |
| is_active | Whether the program is available |

A program belongs to a college and may optionally be assigned to a department. Department assignments remain configurable because the public program directory does not establish every program's department mapping.

### program_majors
Stores optional majors or specializations under an academic program.

| Field | Purpose |
|---|---|
| id | Primary key |
| program_id | Foreign key to programs.id |
| name | Major or specialization name |
| is_active | Whether the major is available for new assignments |

### year_levels
Stores the year levels configured by the Administrator.

| Field | Purpose |
|---|---|
| id | Primary key |
| level_number | Unique numeric year-level identifier |
| name | Display name, such as First Year |
| is_active | Whether the year level can be assigned |

### program_year_levels
Maps year levels permitted by each program. A program can define a different duration from another program.

| Field | Purpose |
|---|---|
| id | Primary key |
| program_id | Foreign key to programs.id |
| year_level | References year_levels.level_number |

Existing program/year combinations are backfilled from section and student records when the academic-structure migration runs.

### sections
Stores section groups under a program and year level.

| Field | Purpose |
|---|---|
| id | Primary key |
| program_id | Foreign key to programs.id |
| year_level | Foreign key to year_levels.level_number |
| code | Section name or code |
| is_active | Whether the section can be assigned |

Section code is unique within a program and year level. New sections must use a year level configured for the selected program.

### students
Stores student-specific profile and academic identifiers.

| Field | Purpose |
|---|---|
| id | Primary key |
| user_id | Foreign key to users.id, unique |
| student_number | Unique student number |
| program_id | Foreign key to programs.id |
| program_major_id | Nullable foreign key to program_majors.id |
| section_id | Nullable foreign key to sections.id |
| year_level | Numeric year-level identifier linked to year_levels.level_number |
| status | Active, inactive, graduated, or other approved status |
| created_at / updated_at | Timestamps |

### subjects
Stores subject catalog information.

| Field | Purpose |
|---|---|
| id | Primary key |
| program_id | Nullable foreign key to programs.id, if program-specific |
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
| semester | First Semester, Second Semester, or Midyear |
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

## 2. Relationships

- One college has many departments and programs.
- One department belongs to one college and may have many programs.
- One program belongs to one college and optionally one department.
- One program has many students, subjects, sections, and majors.
- Programs and year levels have a many-to-many relationship through `program_year_levels`.
- One year level may be assigned to many students and sections and may be supported by many programs.
- One section belongs to one program and year level and may be assigned to many students.
- One student may be assigned to one program major and one section.
- One student has many enrollment records.
- One enrollment has many enrollment-subject records.
- One subject may appear in many enrollment-subject records.
- One enrollment-subject record may have one or more schedule entries.
- One user may create many audit log entries.

## 3. Relationship sketch

```text
colleges
  | 1-to-many
  +---- departments
  |          | 1-to-many
  |          v
  +------ programs ------ 1-to-many ------ program_majors
             |
             +------ 1-to-many ------ students
             |
             +------ 1-to-many ------ sections ------ many-to-1 ------ year_levels
             |
             +------ many-to-many ---- program_year_levels ---- many-to-1 ---- year_levels
             |
             +------ 1-to-many ------ subjects

students
  |
  | 1-to-many
  v
enrollments
  |
  | 1-to-many
  v
enrollment_subjects ------ many-to-1 ------ subjects
  |
  | 1-to-many
  v
class_schedules

users ------ 1-to-many ------ audit_logs
```

## 4. Remaining planned data requirement

Student visitor time-out recording remains pending until the exact workflow and required data fields are confirmed. No visitor table has been added yet, to avoid assuming fields or processes that are not specified in the approved brief.

## 5. Database integrity and privacy rules

- Add unique constraints to user email, student number, program code, department code, and supported academic structure combinations.
- Prevent duplicate enrollment terms for the same student using a unique constraint on student, academic year, and semester.
- Prevent duplicate subject entries within one enrollment using a unique constraint on enrollment and subject.
- Add foreign keys and indexes to relationship columns.
- Use database transactions when creating an enrollment and its subject records.
- Do not store plaintext passwords.
- Avoid adding personal or financial fields unless the project requirements need them.
- The Student Portal must query records through the authenticated student's relationship, not through unrestricted IDs.
- Final grades are stored as nullable decimal values. The allowed minimum and maximum are configured through `SIMS_GRADE_MIN` and `SIMS_GRADE_MAX` in the local environment.
