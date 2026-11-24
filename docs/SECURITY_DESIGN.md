# Security Design

This document describes the security controls used to protect student information and enforce the approved role-permission matrix.

## 1. Authentication architecture

The application uses one Laravel authentication backend and one users table, with separate login pages:

- `/admin/login` for Administrators and authorized Staff.
- `/student/login` for Students.

The login handlers verify the account role before creating an authenticated session. Student accounts cannot sign in through the Admin/Staff portal, and Admin/Staff accounts cannot sign in through the Student Portal. Only the three roles from the approved brief are supported: Administrator, Staff/Registrar, and Student.

Passwords are stored using Laravel's password hashing support. Separate password stores are not used.

## 2. Authorization

Server-side middleware and controller checks enforce role permissions.

- `/admin/*`: authenticated Administrator or Staff, with administrator-only restrictions for account controls, user-role management, and audit logs.
- `/student/*`: authenticated Student only.
- Student-owned data is queried through the authenticated user's linked student profile.

A student cannot access another student's enrollment or profile by changing a URL or submitted identifier. The Student Portal uses the authenticated student's own Eloquent relationships for private records.

## 3. Security controls

| Risk / area | Implemented control |
|---|---|
| Weak password storage | Laravel hashed password cast |
| Cross-Site Request Forgery | Laravel CSRF middleware and Blade form tokens |
| SQL injection | Eloquent and parameterized query-builder operations |
| Broken access control | Server-side role middleware and ownership checks |
| Brute-force login attempts | Login rate limiting |
| Session fixation | Session regeneration after login and invalidation on logout |
| Stored or reflected XSS | Blade escaped output |
| Sensitive data exposure | Input validation and limited collection of personal data |
| Unauthorized record changes | Role checks, validation, and audit logging |
| Administrator lockout | Prevent self-deactivation and removal of the last active administrator |

## 4. Audit logging

The system records important actions, including:
- Authentication activity.
- Student record creation and updates.
- Student account activation/deactivation.
- Enrollment record creation.
- Program and section maintenance.
- Student Visitor Time-Out recording.
- Staff account and role changes.

Audit descriptions do not include passwords or session tokens. Only Administrators can access the audit-log viewer.

## 5. Data protection

- Use fictional data while developing and demonstrating the project.
- Collect only information required by the approved project scope.
- Protect database backups and local environment files.
- Restrict production debug output.
- Use HTTPS when deployed.
- Enforce role restrictions for viewing, updating, exporting, and deleting records.

## 6. Security testing checklist

- [ ] A student cannot open an Admin/Staff route.
- [ ] A student cannot access another student's profile or enrollment by changing an ID.
- [ ] A student cannot modify official records through crafted POST, PUT, PATCH, or DELETE requests.
- [ ] Staff cannot access administrator-only user and role management or the audit-log viewer.
- [ ] Student and Staff accounts cannot access Admin-only user management or audit routes.
- [ ] An administrator cannot deactivate their own account or remove the last active administrator.
- [ ] Unauthenticated users are redirected to the correct login page.
- [ ] Login attempts are rate-limited.
- [ ] Sessions are regenerated after login and invalidated on logout.
- [ ] Forms reject invalid input and include CSRF protection.
- [ ] Stored user content is escaped when displayed.
- [ ] Audit logs capture important record changes without storing secrets.
