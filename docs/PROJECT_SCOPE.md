# Project Scope

This document follows the approved **Student Information Management System** project brief for the College of Engineering and Information Technology, Cavite State University. The approved PDF is the source of truth for the system's roles, modules, and security requirements.

## 1. Project objective

Develop a web-based application that centralizes student records in a database. Authorized personnel can register students, update personal information, manage courses and sections, and generate student reports. Students can log in to view personal and academic information.

The system aims to improve record management, reduce manual paperwork, and protect confidential student information through secure login, role-based access control, and activity monitoring.

## 2. Official roles and permissions

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

### Role responsibilities

- **Administrator:** student and staff accounts, roles, account activation/deactivation, authorized student-record deletion, reports, and activity-log access.
- **Staff / Registrar:** register students, update authorized student information, manage enrollment records, assign programs/courses and sections, search records, and generate student lists and reports.
- **Student:** view personal and academic information, update permitted personal information, change their own password, and use the Visitor Time-Out function.

All restrictions are enforced by server-side middleware and controller validation.

## 3. Required modules

### 3.1 Student Module
- Secure login and logout.
- View personal profile.
- View student number and enrollment information.
- View program/course and year level.
- View assigned section.
- View academic information, when included in the project scope.
- Record visitor time-out.
- Update permitted personal information.
- Change account password.

### 3.2 Administrator Module
- Admin login.
- Dashboard displaying the total number of registered students.
- Add, edit, view, and manage student records.
- Create and manage student accounts.
- Manage courses and programs.
- Manage year levels and sections.
- Activate or deactivate student accounts.
- Search and filter student records.
- Generate student information reports.
- View system activity logs.
- Manage staff accounts and permissions.

### 3.3 Staff / Registrar Module
- Register new students.
- Update student profiles.
- Manage enrollment information.
- Assign students to courses and sections.
- Search student records.
- Generate student lists.
- View and update authorized student information.

## 4. Required security controls

1. Password hashing.
2. Authentication.
3. Role-Based Access Control.
4. Data confidentiality.
5. SQL injection prevention using safe ORM/query-builder methods.
6. Input validation.
7. Secure session management.
8. Activity logs.
9. Administrator account management and deactivation.
10. Data integrity protections.

## 5. Workflow from the approved brief

1. The Administrator creates staff accounts and configures the system.
2. The Registrar logs in.
3. The Registrar registers a new student.
4. The Registrar enters the student's personal and academic information.
5. The system saves the information in the database.
6. The student receives or is assigned an account.
7. The student logs in and views personal information.
8. The Administrator monitors student records and system activities.

## 6. Implementation notes

The system is kept focused on the modules and permission levels listed in the approved PDF. The implementation includes student records, programs/courses, year levels, sections, enrollment history, reports, audit logs, and the Student Portal.

The PDF names the Visitor Time-Out function but does not define its exact data-entry workflow. The current implementation records a server-generated timestamp for the authenticated student's own account. It does not collect visitor names or other visitor details. The intended workflow should still be confirmed with the instructor.

## 7. Data integrity rules

- Never expose one student's private record to another student.
- Student self-service must not permit changes to student number, program, year level, section, or enrollment records.
- Staff must not delete student records or manage student account activation.
- Only Administrators can manage user roles and view activity logs.
- Do not permanently delete student records that have enrollment or visitor time-out history.
- Use database transactions for related record creation and updates.
- Use fictional student records during development and demonstrations.
