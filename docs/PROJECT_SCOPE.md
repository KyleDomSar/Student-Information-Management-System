# Project Scope

This document follows the approved **Student Information Management System** project brief for the College of Engineering and Information Technology, Cavite State University. The approved brief is the source of truth for the required roles, functions, and security controls.

## 1. Project objective

Develop a web-based application that centralizes student records in a database. Authorized personnel can register students, update personal information, manage courses and sections, and generate student reports. Students can log in to view personal and academic information.

The system aims to improve record management, reduce manual paperwork, and protect confidential student information through secure login, role-based access control, and activity monitoring.

## 2. Official user roles and permissions

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

Permission rules:
- **Administrator:** full system administration, student account management, role management, deletion, reports, and activity-log access.
- **Staff / Registrar:** register students, update authorized student information, manage enrollment information, assign students to courses and sections, search records, and generate student lists and reports.
- **Student:** view personal and academic information, update only permitted personal information, change their own password, and record visitor time-out. The exact actor and data-entry workflow are not defined in the PDF, so the current implementation is documented separately below.

All restrictions must be enforced on the backend for every route and record operation.

## 3. Required system modules

### 3.1 Student Module
- Secure login and logout.
- View personal profile.
- View student number and enrollment information.
- View course and year level.
- View assigned section.
- View academic information, if included in the project scope.
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

The project must demonstrate:
1. **Password Hashing:** securely store user passwords.
2. **Authentication:** require users to log in before accessing the system.
3. **Role-Based Access Control:** assign different permissions to students, staff, and administrators.
4. **Data Confidentiality:** protect personal and academic information from unauthorized access.
5. **SQL Injection Prevention:** use prepared statements or safe ORM/query-builder methods.
6. **Input Validation:** validate user inputs.
7. **Session Management:** secure user sessions.
8. **Activity Logs:** record important system activities.
9. **Account Management:** allow administrators to manage and deactivate accounts.
10. **Data Integrity:** prevent unauthorized changes or deletion of records.

## 5. Workflow from the approved brief

1. The Administrator creates staff accounts and configures the system.
2. The Registrar logs in.
3. The Registrar registers a new student.
4. The Registrar enters the student's personal and academic information.
5. The system saves the information in the database.
6. The student receives or is assigned an account.
7. The student logs in and views personal information.
8. The Administrator monitors student records and system activities.

## 6. Current implementation status

### 6.1 Core requirements from the approved PDF
- Centralized authentication with separate Admin/Staff and Student portals, supporting the three approved roles.
- Student registration and student-record management by authorized personnel.
- Student profile and academic-information viewing, limited personal-information updates, and password change.
- Administrator-only student account activation/deactivation, staff account and role management, permanent deletion where allowed, and activity-log access.
- Staff/Registrar student search, authorized profile and enrollment updates, course/section assignment, and student information reports.
- Program/course, subject, year-level, and section catalogs.
- Student visitor time-out recording, with server-generated timestamps, private student history, and audit logging.
- Security controls including password hashing, authentication, role checks, input validation, session management, and audit logging.

### 6.2 Supplementary features implemented beyond the explicit PDF list
These features are present in the prototype but are not stated as mandatory modules or detailed workflows in the approved brief:
- Gradebook for encoding final grades and the corresponding Student Portal grade view.
- Class timetable creation and the corresponding Student Portal schedule view.
- Expanded term enrollment workflow with subject-level entries and pending/enrolled/rejected statuses. The general requirement to manage enrollment information is in scope, but the exact approval lifecycle is an added workflow.
- Expanded CvSU Main Campus academic hierarchy: colleges, departments, majors/specializations, program-specific year-level mappings, and the campus-wide reference catalog. The current seeder includes 11 colleges, 5 CEIT departments, 46 program entries, and 30 major/specialization entries. These details go beyond the minimum program/course, year-level, and section requirements.

These extensions are retained as optional academic enhancements. They must not be described in the project presentation as requirements explicitly stated in the PDF.

### 6.3 Remaining verification and clarification
- Complete route-by-route security testing against every row of the approved role-permission matrix.
- Confirm the intended meaning of "Record visitor time-out" with the instructor. The PDF does not specify whether the record refers to the student, a visitor of the student, or another visitor log process. The current implementation records the authenticated student's own server-generated time-out timestamp and does not collect visitor names, reasons, or destination details.

## 7. Data integrity rules

- Never expose a student's private record to another student.
- Student self-service must not permit changes to student number, program, year level, section, enrollment, or official academic results.
- Staff must not delete student records or manage student account activation.
- Only Administrators can manage user roles and access activity logs.
- Do not permanently delete student records that have enrollment or visitor time-out history.
- Use database transactions for related record creation and updates.
- Use fictional student records during development and demonstrations.
