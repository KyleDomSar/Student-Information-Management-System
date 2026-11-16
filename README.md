# Student Information Management System (SIMS)

A web-based Student Information Management System for academic use. The application will provide separate Admin/Staff and Student interfaces with centralized authentication and role-based access control.

## Project status

**Stage:** Project planning and repository initialization

The repository currently contains the project documentation. The Laravel application will be scaffolded locally and committed after the development environment is checked.

## Technology stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+ and Laravel 13 |
| Frontend | HTML, CSS, JavaScript, Bootstrap 5 |
| Templates | Laravel Blade |
| Database | MySQL |
| Local development | Laragon |
| Version control | Git and GitHub |

Laravel 13 requires PHP 8.3 or higher. Make sure the PHP version enabled in Laragon meets this requirement before creating the application.

## User portals

### Admin / Staff Portal

- Admin dashboard
- Student records management
- Course and program management
- Enrollment management
- Grade encoding and management
- Class schedule management
- User and role management
- Audit log viewing
- Reports

### Student Portal

- View personal profile
- View enrollment status and history
- View grades
- View class schedule
- View account balance, when included in the approved project scope
- Submit document requests, when included in the approved project scope

Students must only be able to view records associated with their own account. They must not be able to edit official student, enrollment, or grade records.

## Authentication and authorization

The system will use **one centralized Laravel authentication system** with separate login pages and dashboards:

- Admin/Staff: `/admin/login`
- Student: `/student/login`

Both login pages authenticate against the same users table. Role-based middleware and Laravel authorization policies will enforce access on the server. Hiding a navigation item or button is not considered access control.

Initial roles:

- `admin`
- `staff`
- `instructor`
- `student`

Student accounts should be created or activated by authorized staff. Public self-registration is disabled for the initial version.

## Initial development phases

1. Confirm the local PHP, Composer, Node.js, and MySQL versions.
2. Scaffold the Laravel application.
3. Configure the MySQL database and migrations.
4. Implement authentication and role-based access.
5. Build separate Admin/Staff and Student layouts.
6. Implement student, course, enrollment, grade, and schedule modules.
7. Add audit logging and security tests.
8. Prepare sample data and project documentation.

## Local setup

After the Laravel application has been scaffolded and pushed to this repository:

1. Clone the repository.
2. Install PHP dependencies with `composer install`.
3. Copy `.env.example` to `.env`.
4. Configure the database credentials in `.env`.
5. Generate the application key with `php artisan key:generate`.
6. Run migrations with `php artisan migrate`.
7. Install frontend dependencies with `npm install`.
8. Start the application using the project's documented development command.

Do not commit `.env`, database passwords, API keys, real student information, or other secrets.

## Documentation

- [Project scope](docs/PROJECT_SCOPE.md)
- [Security design](docs/SECURITY_DESIGN.md)
- [Database overview](docs/DATABASE_OVERVIEW.md)

## Disclaimer

This is an academic project. Use fictional student records during development and demonstrations. Do not use real student personal information without proper authorization and safeguards.
