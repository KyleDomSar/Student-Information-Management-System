# Student Information Management System (SIMS)

A web-based Student Information Management System for the College of Engineering and Information Technology. This project follows the approved project brief and provides separate Administrator/Staff and Student interfaces using one centralized authentication system.

## Technology stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+ and Laravel 13 |
| Frontend | Blade, HTML, CSS, JavaScript, Bootstrap 5 |
| Database | MySQL |
| Local development | Laragon |
| Version control | Git and GitHub |

## Official user roles

The approved project brief defines exactly three roles:

| Function | Student | Staff / Registrar | Administrator |
|---|---:|---:|---:|
| Log in and log out | Yes | Yes | Yes |
| View personal profile | Yes | Yes | Yes |
| Register students | No | Yes | Yes |
| Edit student records | Limited | Yes | Yes |
| Delete student records | No | No | Yes |
| Manage student accounts | No | No | Yes |
| Manage courses and sections | No | Limited | Yes |
| Generate reports | No | Yes | Yes |
| Manage user roles | No | No | Yes |
| View activity logs | No | No | Yes |

All permissions are enforced on the server. Hiding a button in the interface is not treated as an access-control measure.

## Current implementation

### Core requirements from the approved PDF
- Laravel authentication with separate Admin/Staff and Student login pages and the three approved roles: Administrator, Staff/Registrar, and Student.
- Student registration and record management for authorized personnel, including student profiles, student numbers, programs, year levels, sections, and enrollment information.
- Student self-service access to personal and academic information, permitted name/email updates, and password changes with current-password verification.
- Administrator-only student-account activation/deactivation, user-role management, staff-account management, permanent student-record deletion, and activity-log viewing.
- Staff/Registrar student search, authorized record updates, enrollment processing, and student information reports.
- Program/course, subject, year-level, and section catalog functions.
- Student visitor time-out recording, private student history, and audit logging. The current implementation records a server-generated timestamp for the authenticated student.
- Server-side role checks, password hashing, login throttling, session security, validation, CSRF protection, database constraints, and activity logging.

### Supplementary features beyond the explicit PDF requirements
The following are implemented as optional academic extensions. The PDF does not explicitly require these specific workflows or data structures:
- Gradebook entry and student grade views.
- Class timetable creation and student schedule views.
- Detailed term-based enrollment workflow with subject-level enrollment entries and pending/enrolled/rejected approval statuses. Enrollment information management itself is part of the required scope.
- Expanded CvSU Main Campus academic hierarchy, including colleges, departments, majors/specializations, and program-specific year-level mappings. The reference seeder contains 11 Main Campus colleges, 5 CEIT departments, 46 program entries, and 30 major/specialization entries. These expanded reference structures go beyond the PDF's minimum program/course, year-level, and section requirements.

These supplementary modules are retained in the prototype but should be presented as optional enhancements, not as mandatory requirements taken from the PDF.

### Remaining verification and workflow confirmation
- Complete the final route-by-route security review against every row of the approved role-permission matrix.
- Confirm with the instructor what "Record visitor time-out" is intended to mean. The PDF does not define the exact actor or data-entry workflow. The current implementation lets a signed-in student record their own current timestamp and does not collect visitor names or other visitor details.

## Main portal URLs

| Page | URL |
|---|---|
| Portal selection | `/login` |
| Admin/Staff login | `/admin/login` |
| Student login | `/student/login` |
| Admin/Staff dashboard | `/admin/dashboard` |
| Student dashboard | `/student/dashboard` |
| Student profile and personal details | `/student/profile` |
| Student password update | `/student/password` (PUT endpoint) |
| Student visitor time-out history | `/student/visitor-timeouts` |
| Student records | `/admin/students` |
| Academic structure | `/admin/academic-structure` |
| Academic programs | `/admin/programs` |
| Subject catalog | `/admin/subjects` |
| Year level management | `/admin/year-levels` |
| Section management | `/admin/sections` |
| Student information reports | `/admin/reports/students` |
| Enrollment management | `/admin/enrollments` |
| Admin Gradebook | `/admin/grades` |
| Admin class schedules | `/admin/schedules` |
| Admin user management | `/admin/users` |
| Admin audit logs | `/admin/audit-logs` |

## Local setup on Windows / Laragon

1. Clone the repository into `C:\laragon\www\SIMS`.
2. Open Laragon Terminal in the project directory.
3. Install PHP dependencies:

   ```bash
   composer install
   ```

4. Create your local environment file if it does not exist:

   ```cmd
   copy .env.example .env
   ```

5. Set your local MySQL values in `.env`. The example file uses the local database name `sims_db`.
6. Generate the application key:

   ```bash
   php artisan key:generate
   ```

7. Run database migrations:

   ```bash
   php artisan migrate
   ```

8. Seed the initial CvSU Main Campus college and CEIT department reference list, plus any configured local accounts:

   ```bash
   php artisan db:seed
   ```

9. Install frontend dependencies and build assets:

   ```bash
   npm install
   npm run build
   ```

10. Create the initial local Administrator account by setting the values below in your untracked `.env` file, then run `php artisan db:seed` again if the credentials were added after the first seed.
11. Start Laravel:

    ```bash
    php artisan serve
    ```

12. Open `http://127.0.0.1:8000/login`.
13. Run automated tests before considering a change verified:

    ```bash
    php artisan test
    ```

## Create the initial local Administrator account

The seeder creates or updates the Administrator account only when both credentials are set in your **local, untracked** `.env` file:

```env
SIMS_ADMIN_NAME="SIMS Administrator"
SIMS_ADMIN_EMAIL=admin@example.test
SIMS_ADMIN_PASSWORD="replace-with-a-strong-local-password"
```

Then run:

```bash
php artisan config:clear
php artisan db:seed
```

Optional demo Student account:

```env
SIMS_STUDENT_NAME="Demo Student"
SIMS_STUDENT_EMAIL=student@example.test
SIMS_STUDENT_PASSWORD="replace-with-a-different-strong-password"
SIMS_STUDENT_NUMBER=DEMO-0001
```

Use fictional records only. These are local configuration examples, not pre-created credentials.

## Security notes

- Never commit `.env`, passwords, API keys, database credentials, or real student information.
- Passwords are hashed by Laravel's hashed password cast.
- Login attempts are rate-limited.
- Session IDs are regenerated after login and invalidated on logout.
- CSRF protection is provided by Laravel's web middleware and Blade form tokens.
- Student-owned records are queried through the authenticated user's student relationship.
- Student self-service accepts only name and email. Academic identifiers and records remain staff-controlled.
- Only Administrators can manage user roles, activate/deactivate student accounts, delete eligible student records, and view audit logs.
- Permanent student deletion is blocked when enrollment or visitor time-out history exists.

## Documentation

- [Project scope](docs/PROJECT_SCOPE.md)
- [Security design](docs/SECURITY_DESIGN.md)
- [Database overview](docs/DATABASE_OVERVIEW.md)
- [Authentication setup](docs/AUTHENTICATION_SETUP.md)

## Disclaimer

This is an academic project. Use fictional student records during development and demonstrations. Do not use real student personal information without proper authorization and safeguards.
