# Security Design

This document defines the minimum security controls for the academic SIMS project.

## 1. Authentication architecture

Use one Laravel authentication backend and one users table. Present separate entry pages:

- `/admin/login` for administrators and authorized staff.
- `/student/login` for students.

The login handler must verify that the account belongs to the correct role group before creating an authenticated session. A student account must not be able to sign in through the Admin/Staff login page, and an Admin/Staff account must not be sent to the Student Portal.

Do not create two independent password stores. Centralized authentication avoids duplicate account records and inconsistent password policies.

## 2. Authorization

Use server-side middleware and Laravel Policies/Gates.

Suggested route groups:

- `/admin/*`: authenticated admin or staff, with per-action permission checks.
- `/student/*`: authenticated student only.

For student-owned data, scope queries to the authenticated student's profile. Never trust a student ID supplied by a URL, hidden input, or request body without checking ownership.

Example principle:

```php
$student = $request->user()->student;

// Retrieve only the authenticated student's enrollment records.
$enrollments = $student->enrollments()->with('subjects')->get();
```

This is a conceptual example. The actual relationship names will follow the implemented models.

## 3. Required controls

| Risk / area | Minimum control |
|---|---|
| Weak password storage | Use Laravel's Hash facade and supported password hashing configuration |
| Cross-Site Request Forgery | Keep CSRF protection enabled for state-changing web forms |
| SQL injection | Use Eloquent or parameterized queries; never concatenate raw user input into SQL |
| Broken access control | Enforce middleware, Policies/Gates, and record ownership checks server-side |
| Brute-force login attempts | Apply rate limiting to login endpoints |
| Session theft / fixation | Regenerate the session after successful login; invalidate the session on logout |
| Stored or reflected XSS | Escape untrusted output in Blade; avoid rendering untrusted HTML |
| Sensitive data exposure | Validate and minimize collected data; never commit secrets or real student data |
| Unauthorized record changes | Validate requests and record actor, action, and timestamp in audit logs |

## 4. Audit logging

Record security-relevant actions such as:
- Successful and failed login attempts (avoid storing passwords or full credentials).
- Student record creation and updates.
- Enrollment status changes.
- Grade creation and updates.
- User role changes.

Recommended fields:
- Actor user ID (nullable for unauthenticated events).
- Action name.
- Target type and target ID.
- Timestamp.
- A short, sanitized description.
- Optional request metadata only when justified.

Do not place passwords, session tokens, full payment data, or unnecessary personal information in logs.

## 5. Data protection

- Use fictional data while developing and demonstrating the project.
- Collect only fields required by the approved project scope.
- Protect database backups and local environment files.
- Restrict production debug output.
- Use HTTPS when deployed.
- Define who can view, update, export, and delete records.

## 6. Security testing checklist

- [ ] A student cannot open an Admin/Staff route.
- [ ] A student cannot access another student's profile, grades, enrollment, or schedule by changing an ID.
- [ ] A student cannot modify official records through a crafted POST, PUT, PATCH, or DELETE request.
- [ ] Staff cannot access administrator-only user and role management.
- [ ] Unauthenticated users are redirected to the correct login page.
- [ ] Login attempts are rate limited.
- [ ] Sessions are regenerated after login and invalidated on logout.
- [ ] Forms reject invalid input and include CSRF protection.
- [ ] Stored user content is escaped when displayed.
- [ ] Audit logs capture important record changes without storing secrets.
