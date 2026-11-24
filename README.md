# Student Information Management System (SIMS)

A web-based Student Information Management System for the College of Engineering and Information Technology, Cavite State University. The system is focused on the modules and permissions listed in the approved project brief.

## Technology stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+ and Laravel 13 |
| Frontend | Blade, HTML, CSS, JavaScript, Bootstrap 5 |
| Database | MySQL |
| Local development | Laragon |
| Version control | Git and GitHub |

## Official user roles

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

## Implemented system modules

### Student Module
- Secure login and logout.
- View personal profile, student number, program/course, year level, assigned section, and enrollment history.
- Update permitted personal information.
- Change account password.
- Record Visitor Time-Out and view personal time-out history.

### Administrator Module
- Dashboard showing registered students and basic system totals.
- Create, view, update, search, and manage student records.
- Create and manage student accounts.
- Manage programs/courses, year levels, and sections.
- Activate or deactivate student portal accounts.
- Generate and export student information reports.
- Manage staff accounts and permissions.
- View system activity logs.

### Staff / Registrar Module
- Register new students and create their portal accounts.
- Search, view, and update authorized student information.
- Assign students to programs/courses, year levels, and sections.
- Record enrollment information.
- Generate student lists and reports.

## Security controls
- Password hashing and authenticated sessions.
- Server-side role-based access control.
- Login throttling and session regeneration.
- Input validation and CSRF protection.
- Eloquent ORM and database constraints.
- Student record ownership checks.
- Activity logging for important system actions.

## Main portal URLs

| Page | URL |
|---|---|
| Portal selection | `/login` |
| Admin/Staff login | `/admin/login` |
| Student login | `/student/login` |
| Admin/Staff dashboard | `/admin/dashboard` |
| Student dashboard | `/student/dashboard` |
| Student profile | `/student/profile` |
| Student password update | `/student/password` (PUT endpoint) |
| Student visitor time-out history | `/student/visitor-timeouts` |
| Student records | `/admin/students` |
| Programs / Courses | `/admin/programs` |
| Year level management | `/admin/year-levels` |
| Section management | `/admin/sections` |
| Student information reports | `/admin/reports/students` |
| Enrollment records | `/admin/enrollments` |
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

8. Seed the CEIT program/course list, default year levels, and any configured local accounts:

   ```bash
   php artisan db:seed
   ```

9. Install frontend dependencies and build assets:

   ```bash
   npm install
   npm run build
   ```

10. Set the initial local Administrator account values in the untracked `.env` file, then run `php artisan db:seed` again if the credentials were added after the first seed.
11. Start Laravel:

   ```bash
   php artisan serve
   ```

12. Open `http://127.0.0.1:8000/login`.
13. Run automated tests:

   ```bash
   php artisan test
   ```

## Full presentation database (recommended)

For a complete classroom demonstration, use a separate database. This keeps your existing `sims_db` and its records untouched.

1. In Laragon/phpMyAdmin, open the SQL tab and create the new database:

   ```sql
   CREATE DATABASE sims_presentation_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. In your local, untracked `.env`, change only the database name. Keep your existing local host, port, username, and password:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sims_presentation_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. In Laragon Terminal, from `C:\laragon\www\SIMS`, run:

   ```bash
   php artisan config:clear
   php artisan migrate:fresh --seed --seeder=PresentationDatabaseSeeder
   php artisan optimize:clear
   php artisan serve
   ```

   **Important:** `migrate:fresh` drops every table in the database configured by `.env`. Confirm that `DB_DATABASE=sims_presentation_db` before running it. Never run this command while `.env` points to your original `sims_db`.

4. Open `http://127.0.0.1:8000/login`.

The seeder runs only in local/testing environments. It fills the modules currently included in SIMS with fictional records:

| Module | Presentation data |
|---|---:|
| User Management | 1 Admin and 10 Staff / Registrar accounts |
| Student Accounts | 30 accounts, including active, inactive, and graduated profiles |
| Programs / Courses | 10 CEIT programs |
| Year Levels | 6 levels, including one archived inactive level |
| Sections | 102 sections, including inactive examples |
| Student Records | 30 complete profiles across the CEIT programs |
| Enrollment Records | 86 records across multiple academic years and semesters |
| Visitor Time-Out | 33 sample records |
| Audit Logs | 278 sample activity events |
| Reports | Uses all 30 student records and supports all status filters |

The dataset is repeatable. Running the seeder again updates or reuses the same presentation records instead of adding duplicates. It does not run through the normal `DatabaseSeeder`.

**Primary presentation accounts**

| Role | Email | Password |
|---|---|---|
| Admin | `admin.presentation@sims.test` | `Admin@SIMS2026!` |
| Staff / Registrar | `registrar.presentation@sims.test` | `Staff@SIMS2026!` |
| Student | `alyssa.santos.presentation@sims.test` | `Student@SIMS2026!` |

There are 9 additional Staff accounts and 29 additional Student accounts for populated lists and record demonstrations. All Staff accounts use `Staff@SIMS2026!`; all Student accounts use `Student@SIMS2026!`. One Staff account and two Student accounts are intentionally inactive and cannot sign in. See [the presentation dataset roster](docs/PRESENTATION_DATASET.md) for all sample names, account emails, academic assignments, and statuses.

**Primary Student profile**

| Field | Value |
|---|---|
| Name | Alyssa Mae Santos |
| Student number | `2026-CEIT-0001` |
| Program | BSIT |
| Year level | Third Year |
| Section | 3A |
| Student status | Active |
| Enrollments | 3 records |
| Visitor Time-Out | 3 records |

To return to your original local database later, change `DB_DATABASE` back to `sims_db` in `.env`, then run `php artisan config:clear`.

## Optional bulk demo data for an existing database


This older opt-in seeder is for adding a larger sample dataset to an existing local database. Do not run it in the clean presentation database above when you want exactly three accounts.

It creates or updates a dedicated demo Registrar account, 11 demo Student accounts, demo Sections under currently active CEIT Programs and Year Levels, sample enrollment history, Visitor Time-Out entries, and clearly labeled `[DEMO]` audit-log descriptions.

Run it only when you want sample data in your local database:

```bash
php artisan db:seed --class=DemoPresentationSeeder
```

The seeder:
- Is not called by the normal `php artisan db:seed` command.
- Runs only when `APP_ENV=local` or `APP_ENV=testing`.
- Uses reserved `.test` email addresses and `SIMS-DEMO-2026-` student numbers.
- Uses existing active Programs and Year Levels. It does not reactivate inactive academic records.
- Is repeatable. Running it again updates the same demo records instead of adding duplicates.
- Does not delete, reset, or overwrite non-demo student records.
- Does not require a migration.

**Local demo logins**

| Portal | Email | Password |
|---|---|---|
| Admin / Staff | `registrar.demo@sims.test` | `Demo@2026!` |
| Student | `alyssa.santos.demo@sims.test` | `Demo@2026!` |

Use your existing Administrator account for the Admin portal. The demo password is for local presentation only. Do not deploy these demo credentials or use them for real student information.

## Offline local use after one-time setup

SIMS can run on your own computer without an internet connection after its PHP and Node dependencies are installed and its frontend assets are built once.

While internet is available, complete the local setup steps above, including:

```bash
composer install
npm install
npm run build
```

Bootstrap CSS, Bootstrap Icons, and Bootstrap JavaScript are bundled into the local Vite build. The login pages and authenticated portals do not need a CDN connection at runtime. The interface uses system fonts, so it does not need a remote font service either.

For normal offline use:

1. Start Laragon and make sure its local MySQL service is running.
2. Open Laragon Terminal in `C:\laragon\www\SIMS`.
3. Start the app:

   ```bash
   php artisan serve
   ```

4. Open `http://127.0.0.1:8000/login`.

The application records are stored in the local MySQL database configured by `DB_DATABASE` in `.env`, such as `sims_db` or `sims_presentation_db`. Stopping `php artisan serve` does not delete database records. Starting the server again reconnects to the same database. Stopping Laragon/MySQL also does not normally delete the database; keep the Laragon data directory intact.

Do not use `php artisan migrate:fresh`, `php artisan db:wipe`, or delete the Laragon MySQL data directory on a database that contains records you need to keep. Back up `sims_db` regularly using phpMyAdmin Export or `mysqldump`.

When you change frontend CSS or JavaScript, rebuild assets with `npm run build`. That build step and installing dependencies may need internet access the first time or when dependencies change, but serving the already-built local app does not require internet.

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
- Student-owned information is queried through the authenticated user's student relationship.
- Student self-service accepts only name and email. Academic identifiers and enrollment records remain staff-controlled.
- Only Administrators can manage user roles, activate/deactivate student accounts, delete eligible student records, and view audit logs.
- Permanent student deletion is blocked when enrollment or visitor time-out history exists.

## Documentation
- [Project scope](docs/PROJECT_SCOPE.md)
- [Security design](docs/SECURITY_DESIGN.md)
- [Database overview](docs/DATABASE_OVERVIEW.md)
- [Authentication setup](docs/AUTHENTICATION_SETUP.md)

## Disclaimer
This is an academic project. Use fictional student records during development and demonstrations. Do not use real student personal information without proper authorization and safeguards.
