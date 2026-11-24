# Authentication Setup

## Portal URLs

- General login selection: `/login`
- Admin/Staff login: `/admin/login`
- Student login: `/student/login`

Both portals use Laravel's single session guard and the same `users` table. The role field determines which portal and actions an account may access.

## Official roles

- `admin`: Administrator. Full system administration, user and role management, student account activation/deactivation, eligible student-record deletion, and audit-log access.
- `staff`: Staff / Registrar. Student registration, authorized student-profile updates, enrollment workflows, and permitted registrar functions.
- `student`: Student Portal access, permitted personal-information updates, and self-service password change.

The application supports only these three roles. Unsupported legacy role values are not authorized to enter either portal.

## Create the first Administrator

1. Copy `.env.example` to `.env` if this is a fresh clone.
2. Set a unique local Administrator email and a strong password in the local `.env` file:

```env
SIMS_ADMIN_NAME="SIMS Administrator"
SIMS_ADMIN_EMAIL=your-admin-email@example.test
SIMS_ADMIN_PASSWORD="use-a-strong-local-password"
```

3. Run:

```bash
php artisan config:clear
php artisan db:seed
```

The seeder does not create an Administrator if either credential is missing. Do not commit the real `.env` file or share its password.

## Authentication and authorization behavior

- Separate Admin/Staff and Student login screens use one authentication backend.
- The login handler verifies the role group before creating an authenticated session.
- Login attempts are rate-limited.
- The session ID is regenerated after login.
- Logout invalidates the session and regenerates the CSRF token.
- Inactive accounts cannot sign in.
- Student routes require the `student` role.
- Admin/Staff routes require the `admin` or `staff` role, with Administrator-only actions protected by an additional `role:admin` middleware.
- Staff cannot change student-account activation status, delete student records, maintain the program/subject catalogs, manage user roles, or view audit logs.
- Students can update only their own name and email through the profile page. Official academic information remains controlled by authorized school personnel.
- Students can change their own password after verifying their current password.
- Student enrollment, grade, and timetable queries are scoped to the authenticated student's own profile.

## Audit events

Login and logout events, failed login attempts, profile updates, password changes, and implemented administrative/academic record changes are recorded without storing passwords or session secrets.

## Remaining authentication/security work

Continue testing every route against the approved Administrator, Staff / Registrar, and Student permission matrix. Add the remaining year-level, section, visitor time-out, and reporting modules without weakening role restrictions.
