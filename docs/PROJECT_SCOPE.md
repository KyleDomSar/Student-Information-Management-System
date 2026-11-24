# Project Scope

## 1. Project objective

Build a straightforward web-based Student Information Management System (SIMS) that centralizes student academic records while separating administrative tasks from student self-service access.

## 2. User roles and permissions

| Feature | Admin | Staff / Registrar | Instructor | Student |
|---|---:|---:|---:|---:|
| Manage user accounts and roles | Yes | No | No | No |
| View all student records | Yes | Yes | Assigned classes only | Own record only |
| Create and update student records | Yes | Yes | No | No |
| Manage programs and subjects | Yes | Yes | No | No |
| Manage enrollment records | Yes | Yes | View assigned classes | View own enrollment |
| Encode or update grades | Yes | Authorized only | Assigned classes only | No |
| View grades | Yes | Yes | Assigned classes | Own grades only |
| Manage class schedules | Yes | Yes | Assigned classes only | No |
| View class schedule | Yes | Yes | Assigned classes | Own schedule only |
| View audit logs | Yes | Limited, if authorized | No | No |

**Important:** Permissions must be checked on the server for every protected route and record operation. Frontend visibility is not a security boundary.

## 3. Initial modules

### Authentication
- Separate Admin/Staff and Student login pages.
- Shared Laravel authentication backend and users table.
- Password hashing, session regeneration after login, logout invalidation, and login throttling.
- No public student self-registration in the initial release.

### Admin / Staff portal
- Dashboard with simple summary cards.
- Student records management (search, create, view, update, and safe account deactivation).
- Academic program management (create, list, update, and safe deactivation).
- Subject catalog management (search, create, update, and safe deactivation).
- Enrollment management: create term records, attach subjects, and approve or reject pending enrollment.
- Grade management: Admin/Staff encode and update numeric final grades for approved enrollments; students can only view their own grades.
- Class schedule management: assign meeting days, times, and rooms to approved enrollment subjects.
- User and role management for administrators.
- Audit log viewer.

### Student portal
- Personal dashboard.
- Read-only profile.
- Enrollment status and history (read-only, restricted to the signed-in student's own records).
- Grades (read-only, limited to the signed-in student's approved enrollments).
- Class schedule (read-only, restricted to approved enrollments belonging to the signed-in student).
- Optional document request and account-balance features only if required by the instructor.

## 4. Initial database entities

- Users
- Students
- Staff profiles (only if needed)
- Programs
- Subjects
- Enrollments
- Enrollment subjects
- Class schedules
- Audit logs

See [Database Overview](DATABASE_OVERVIEW.md) for the initial relationships.

## 5. Implementation status

Implemented in the repository: centralized portal authentication, role middleware, student profile management, academic program management, subject catalog management, enrollment creation and approval, student-owned enrollment views, Admin/Staff grade encoding, student-owned read-only grade views, class schedule management, student-owned timetable views, Admin-only user management, Admin-only audit log viewing, audit event recording for the implemented actions, and automated feature tests.

Planned next: reports, optional document requests/tuition information, and any instructor workspace required by the final project scope.

## 6. Explicitly out of scope for the first milestone

To keep the project achievable, the first milestone will not include:
- Online payment processing or storage of card details.
- Public student account registration.
- Automated SMS or email gateways.
- Integration with an existing university information system.
- Complex analytics or learning management features.

These can be considered later if they are required.

## 7. Acceptance criteria for the first milestone

- Admin/Staff and Student have separate login pages and dashboards.
- A student cannot access administrative routes.
- A student cannot view another student's data by changing an ID in the URL.
- A student cannot create, update, or delete official academic records.
- Authorized staff can manage student and academic records.
- Passwords are never stored in plaintext.
- Important record changes create audit log entries.
- The app uses fictional sample data during development.
