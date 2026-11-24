# Database Overview

This document describes the database tables used by the current SIMS implementation.

## 1. Core tables

### users
Stores account credentials and assigned roles.

| Field | Purpose |
|---|---|
| id | Primary key |
| name | Account holder name |
| email | Unique login email |
| password | Hashed password |
| role | admin, staff, or student |
| is_active | Whether the account is enabled |
| created_at / updated_at | Timestamps |

### staff_profiles
Stores optional staff identity details linked to an account.

| Field | Purpose |
|---|---|
| id | Primary key |
| user_id | Foreign key to users.id |
| employee_number | Optional employee number |
| position | Staff position |
| department | Department label |
| created_at / updated_at | Timestamps |

### programs
Stores programs or courses assigned to students.

| Field | Purpose |
|---|---|
| id | Primary key |
| code | Program code |
| name | Program/course name |
| description | Optional description |
| is_active | Whether the program is available |
| created_at / updated_at | Timestamps |

### year_levels
Stores the year levels configured by the Administrator.

| Field | Purpose |
|---|---|
| id | Primary key |
| level_number | Numeric year-level identifier |
| name | Display name, such as First Year |
| is_active | Whether the year level can be assigned |

### sections
Stores section groups under a program and year level.

| Field | Purpose |
|---|---|
| id | Primary key |
| program_id | Foreign key to programs.id |
| year_level | Foreign key to year_levels.level_number |
| code | Section name or code |
| is_active | Whether the section can be assigned |

A section belongs to one program and one year level. Its code is unique within that program and year level.

### students
Stores student-specific profile and academic identifiers.

| Field | Purpose |
|---|---|
| id | Primary key |
| user_id | Foreign key to users.id, unique |
| student_number | Unique student number |
| program_id | Nullable foreign key to programs.id |
| section_id | Nullable foreign key to sections.id |
| year_level | Numeric year-level identifier |
| status | Student status |
| created_at / updated_at | Timestamps |

### enrollments
Stores a student's enrollment record for an academic year and semester.

| Field | Purpose |
|---|---|
| id | Primary key |
| student_id | Foreign key to students.id |
| academic_year | Example: 2026-2027 |
| semester | First Semester, Second Semester, or Midyear |
| created_by | User ID of the staff member who recorded the enrollment |
| created_at / updated_at | Timestamps |

A unique constraint prevents duplicate records for the same student, academic year, and semester.

### student_visitor_timeouts
Stores time-out timestamps recorded through the authenticated Student Portal account.

| Field | Purpose |
|---|---|
| id | Primary key |
| student_id | Foreign key to students.id |
| time_out_at | Server-generated time-out date and time |
| created_at / updated_at | Timestamps |

The application uses the authenticated user's linked student profile. A submitted form cannot choose another student ID or alter the server timestamp.

### audit_logs
Stores important administrative and student-account activities.

| Field | Purpose |
|---|---|
| id | Primary key |
| user_id | Nullable actor user ID |
| action | Action name |
| target_type | Affected model or record type |
| target_id | Affected record ID |
| description | Sanitized summary |
| ip_address | Request IP address |
| user_agent | Request user agent |
| created_at | Event timestamp |

## 2. Main relationships

- One user has one student profile or staff profile.
- One program has many students and sections.
- One year level has many students and sections.
- One section belongs to one program and one year level.
- One student has many enrollment records and visitor time-out records.
- One user may create many audit log entries.

## 3. Relationship sketch

```text
users
  | 1-to-1
  +---- students
  |        | many-to-1
  |        +---- programs
  |        |       | 1-to-many
  |        |       +---- sections
  |        |                | many-to-1
  |        |                +---- year_levels
  |        |
  |        +---- enrollments
  |        |
  |        +---- student_visitor_timeouts
  |
  +---- staff_profiles
  |
  +---- audit_logs
```

## 4. Database integrity and privacy rules

- Enforce unique user email, student number, program code, and section code within its program/year level.
- Prevent duplicate enrollment terms for the same student.
- Add foreign keys and indexes to relationship columns.
- Use transactions when creating a student account and profile.
- Do not store plaintext passwords.
- Collect only student data required by the approved project scope.
- Query student-owned records through the authenticated student's relationship, not unrestricted IDs.
- Preserve enrollment and visitor time-out history from permanent student deletion.
