# Authentication Setup

## Portal URLs

- General login selection: `/login`
- Admin/Staff login: `/admin/login`
- Student login: `/student/login`

Both portals use Laravel's single session guard and the same `users` table. The role field determines which portal an account may use.

## Roles

- `admin`: full administrative access.
- `staff`: authorized registrar and staff access.
- `instructor`: reserved for a future instructor workspace. Instructor sign-in is not enabled in the current milestone.
- `student`: Student Portal access only.

Role checks are performed with the `role` route middleware on the server. A student cannot gain staff access by changing a URL or manipulating the frontend.

## Create the first administrator

1. Copy `.env.example` to `.env` if this is a fresh clone.
2. Set a unique local administrator email and a strong password in the local `.env` file:

```env
SIMS_ADMIN_NAME="SIMS Administrator"
SIMS_ADMIN_EMAIL=your-admin-email@example.test
SIMS_ADMIN_PASSWORD="use-a-strong-local-password"
```

3. Run:

```bash
php artisan db:seed
```

The seeder does not create an administrator if either credential is missing. Do not commit the real `.env` file or share its password.

## Current implementation

- Separate Admin/Staff and Student login screens.
- Shared authentication and session handling.
- Role restriction at login and route middleware.
- Login throttling (5 attempts per minute per route/IP).
- Session ID regeneration after login.
- Session invalidation and CSRF token regeneration on logout.
- Login/logout and failed-login audit events.
- Students have dashboard and own-profile read-only routes.

## Not implemented yet

Student account provisioning, user management screens, password-reset workflow, grade editing, enrollment management, and full CRUD modules are planned for later milestones.
