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
- **Student:** view personal and academic information, update only permitted personal information, change their own password, and record visitor time-out as defined by the approved workflow.

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

Implemented:
- Centralized Laravel authentication with separate Admin/Staff and Student login pages.
- Server-side role middleware for the three supported roles: Administrator, Staff / Registrar, and Student.
- Administrator-only user management and activity-log viewing.
- Staff student registration and authorized student-record updates.
- Administrator-only student account activation/deactivation.
- Administrator-only deletion of student records when no enrollment history exists. Deletion is blocked when academic history exists to preserve data integrity.
- Administrator-only program, subject, year-level, and section catalog maintenance; Staff can view catalogs and use existing entries in registrar workflows.
- Students can be assigned to sections that match their program and year level.
- Staff and Administrators can filter student lists and export student information reports as CSV.
- Student self-service updates for name and email.
- Student self-service password change with current-password verification.
- Student-owned enrollment, grade, and class-schedule views.
- Student visitor time-out recording with an automatically generated Asia/Manila timestamp, private history, and audit-log entry.
- Audit events for authentication and implemented record changes.

Remaining work:
- Complete security testing against every row of the approved role matrix.

The visitor time-out implementation intentionally records only the authenticated student's timestamp and does not add visitor names, reasons, destination details, or an approval workflow because those details are not specified in the approved brief. The gradebook and timetable are supplementary academic-information features in the current prototype.

## 7. Data integrity rules

- Never expose a student's private record to another student.
- Student self-service must not permit changes to student number, program, year level, section, enrollment, or official academic results.
- Staff must not delete student records or manage student account activation.
- Only Administrators can manage user roles and access activity logs.
- Do not permanently delete student records that have enrollment or visitor time-out history.
- Use database transactions for related record creation and updates.
- Use fictional student records during development and demonstrations.
