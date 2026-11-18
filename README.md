# Student Information Management System (SIMS)

A web-based Student Information Management System for academic use. SIMS has separate Admin/Staff and Student portals backed by one centralized authentication system and server-side role-based access control.

## Technology stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+ and Laravel 13 |
| Frontend | Blade, HTML, CSS, JavaScript, Bootstrap 5 |
| Database | MySQL |
| Local development | Laragon |
| Version control | Git and GitHub |

## Current implementation

### Implemented foundation
- Laravel 13 application structure.
- MySQL configuration for the project's PHP 8.3 runtime.
- Shared `users` authentication model with `admin`, `staff`, `instructor`, and `student` roles.
- Separate Admin/Staff and Student login pages.
- One session-based authentication backend.
- Server-side role middleware for protected portal routes.
- Login throttling, session regeneration, secure logout, and login activity audit events.
- Initial database models and migrations for students, staff profiles, programs, subjects, enrollments, class schedules, and audit logs.
- Admin/Staff dashboard with basic record counts and recent audit activity.
- Student dashboard and read-only student profile/enrollment views.
- Admin/Staff student records management: searchable student directory, profile creation, viewing, editing, and safe account deactivation.
- Academic program management: create, view, update, and safe deactivation.
- Subject catalog management: search, create, update, and safe deactivation, with program association.
- Audit events for student, program, and subject record changes.
- Optional environment-configured local seed accounts.

### Still to be developed
- Admin user and role management screens.
- Enrollment processing and approval.
- Grade encoding and student grade viewing.
- Class schedule management.
- Reports and export.
- Document requests and tuition balance, only if required by the final project scope.
- Additional authorization policies and comprehensive security testing.

Enrollment processing, gradebook, audit log browsing, and user management are not implemented yet. Their navigation entries are shown as disabled placeholders.

## Portal URLs

| Page | URL |
|---|---|
| Portal selection | `/login` |
| Admin/Staff login | `/admin/login` |
| Student login | `/student/login` |
| Admin/Staff dashboard | `/admin/dashboard` |
| Student dashboard | `/student/dashboard` |
| Student profile | `/student/profile` |
| Student records | `/admin/students` |
| Academic programs | `/admin/programs` |
| Subject catalog | `/admin/subjects` |

Both portals use the same `users` table and Laravel session guard. The login controller checks the account role, while route middleware enforces access on the server. Students have no routes for editing official academic records. The instructor role is reserved for a future instructor workspace and is not yet allowed to sign in.

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

5. Set your local MySQL values in `.env`. The default example is configured for Laragon's local MySQL and database `sims_db`.
6. Generate the application key:

   ```bash
   php artisan key:generate
   ```

7. Run database migrations:

   ```bash
   php artisan migrate
   ```

8. Install frontend dependencies and build assets if needed:

   ```bash
   npm install
   npm run build
   ```

9. Start Laravel:

   ```bash
   php artisan serve
   ```

10. Open `http://127.0.0.1:8000/login` or use the Laragon virtual host if configured.

## Create local demo accounts

The seeder creates accounts only when the corresponding values are set in your **local, untracked** `.env` file.

For an administrator:

```env
SIMS_ADMIN_NAME="SIMS Administrator"
SIMS_ADMIN_EMAIL=admin@example.test
SIMS_ADMIN_PASSWORD="replace-with-a-strong-local-password"
```

Optional demo student:

```env
SIMS_STUDENT_NAME="Demo Student"
SIMS_STUDENT_EMAIL=student@example.test
SIMS_STUDENT_PASSWORD="replace-with-a-different-strong-password"
SIMS_STUDENT_NUMBER=DEMO-0001
```

Then run:

```bash
php artisan db:seed
```

Use fictional records only. These demo values are local configuration examples, not pre-created GitHub credentials.

## Security notes

- Never commit `.env`, passwords, API keys, database credentials, or real student information.
- Passwords are hashed by Laravel's hashed password cast.
- Login attempts are rate-limited.
- Session IDs are regenerated after login and invalidated on logout.
- CSRF protection is provided by Laravel's web middleware and Blade form tokens.
- Student records are retrieved through the authenticated user's student relationship.
- Backend authorization is required; hiding a UI control does not secure a route.

## Documentation

- [Project scope](docs/PROJECT_SCOPE.md)
- [Security design](docs/SECURITY_DESIGN.md)
- [Database overview](docs/DATABASE_OVERVIEW.md)
- [Authentication setup](docs/AUTHENTICATION_SETUP.md)

## Disclaimer

This is an academic project. Use fictional student records during development and demonstrations. Do not use real student personal information without proper authorization and safeguards.
