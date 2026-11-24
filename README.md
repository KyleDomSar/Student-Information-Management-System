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

### Implemented foundation
- Laravel 13 application structure and MySQL database configuration.
- Centralized authentication using one `users` table and Laravel session guard.
- Separate Admin/Staff and Student login pages.
- Official app roles: `admin`, `staff`, and `student`. Unsupported legacy role values cannot access either portal.
- Login throttling, session regeneration, secure logout, password hashing, and authentication audit events.
- Admin-only user and role management.
- Admin-only audit log viewer.
- Admin/Staff dashboard and Student dashboard.
- Student registration and record search, view, and update for authorized personnel.
- Admin-only student account activation/deactivation.
- Admin-only permanent deletion for student records without enrollment history. Deletion is blocked when academic history exists.
- Admin-only program and subject catalog maintenance. Staff can view the catalogs and use existing entries in authorized registrar workflows.
- CvSU Main Campus academic hierarchy: colleges, departments, programs, majors/specializations, and program-specific year levels. The seeder loads 11 Main Campus colleges, 5 CEIT departments, 46 program entries, and the listed majors/specializations. Program codes are local SIMS identifiers pending verification against registrar records; department and year-level assignments are left configurable.
- Enrollment terms follow the CvSU calendar labels: First Semester, Second Semester, and Midyear.
- Student self-service update of permitted personal details (name and email).
- Student self-service password change requiring the current password.
- Students cannot edit their student number, program, major, year level, student status, enrollment, grades, or schedules.
- Enrollment workflow: authorized staff create term enrollments, attach active subjects, and approve or reject pending requests.
- Gradebook for Admin/Staff to encode and update numeric final grades for approved enrollments. The prototype grade range is configurable in `.env`.
- Read-only Student Portal enrollment, grade, and class schedule views scoped to the signed-in student's own records.
- Student visitor time-out recording with server-generated Asia/Manila timestamps, private self-service history, and audit logging.
- Duplicate student term prevention and subject-to-program validation.
- Audit events for student record changes, account status changes, enrollment, grades, catalog changes, authentication, visitor time-out recording, and student self-service updates.

### Required work still in progress
- Complete security testing against every row of the approved permission matrix.

The visitor time-out feature currently records the authenticated student's time-out timestamp and keeps a private history. The approved brief does not specify additional visitor details or an approval workflow, so those fields and processes have not been assumed. The gradebook and class timetable are supplementary academic-information features in the current prototype.

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
