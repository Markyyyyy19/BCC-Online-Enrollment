# Buenavista Community College (BCC)
## Online Student Enrollment & Grade Management System
### Technical Project Documentation & System Manual

---

## 1. Project Overview & Objectives

### 1.1 Purpose
The **BCC Online Enrollment System** is a responsive web application engineered for Buenavista Community College to modernize and streamline the academic enrollment and grading lifecycle. It replaces paper-based application queues with role-governed digital workflows for both students and the college registrar.

### 1.2 Core Capabilities
- **Student Portal**: Account registration, multi-program application submission, elective and major track selection, schedule viewing, application withdrawal, and grade inquiries.
- **Registrar Management Portal**: Real-time application triage (review, approve, reject, notes), student account directory with modal profile editing and access control, program catalog administration, curriculum subject catalog management with filters, and two-term grade encoding.
- **Two-Term Grade Calculation**: Streamlined grading using Midterm and Final marks with real-time average calculation:
  $$\text{Average} = \frac{\text{Midterm} + \text{Final}}{2}$$
  Automatic remark assignment: **Passed** ($\le 3.00$), **Failed** ($> 3.00$), or **Incomplete**.
- **RESTful API**: Version 1 endpoints (`/api/v1/`) providing headless programmatic access for catalog, enrollment, student, and grade querying.

---

## 2. System Architecture & Tech Stack

- **Backend Framework**: Laravel 12 (PHP 8.4)
- **Database Engine**: PostgreSQL with relational constraints, triggers, and indices
- **Frontend Layer**: Server-rendered Laravel Blade with pure CSS (zero external bundle dependency)
- **Authentication**: Stateful session auth with bcrypt hashing, role middleware, rate limiting, and CSRF protection
- **API Engine**: Laravel Sanctum / Token-based REST API with JsonResource transformation

### Architecture Flowchart
```mermaid
flowchart TD
    subgraph ClientLayer [Client & Presentation Layer]
        StudentBrowser["Student Browser (Desktop / Mobile)"]
        RegistrarBrowser["Registrar Browser (Desktop / Mobile)"]
        ApiClient["External / API Client"]
    end

    subgraph AppLayer [Application Layer - Laravel 12]
        Middleware["Role & Auth Middleware (EnsureUserRole, RateLimiter)"]
        WebControllers["Web Controllers (Student, Registrar, Auth)"]
        ApiControllers["REST API v1 Controllers & Resources"]
        BladeViews["Blade View Engine & Responsive Portal CSS"]
    end

    subgraph DataLayer [Persistence Layer - PostgreSQL]
        DB[("PostgreSQL Database (bcc_enrollment)")]
    end

    StudentBrowser -->|HTTPS / Session| Middleware
    RegistrarBrowser -->|HTTPS / Session| Middleware
    ApiClient -->|JSON / Bearer Token| Middleware

    Middleware --> WebControllers
    Middleware --> ApiControllers

    WebControllers --> BladeViews
    WebControllers --> DB
    ApiControllers --> DB
    BladeViews --> StudentBrowser
    BladeViews --> RegistrarBrowser
```

---

## 3. Database Schema & Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    USERS ||--o{ ENROLLMENTS : submits
    USERS o|--o{ GRADES : encodes
    PROGRAMS ||--o{ PROGRAM_MAJORS : offers
    PROGRAMS ||--o{ SUBJECTS : contains
    PROGRAMS ||--o{ ENROLLMENTS : selected_for
    PROGRAM_MAJORS o|--o{ SUBJECTS : specializes
    PROGRAM_MAJORS o|--o{ ENROLLMENTS : selected_for
    ENROLLMENTS ||--|{ ENROLLMENT_SUBJECTS : includes
    SUBJECTS ||--o{ ENROLLMENT_SUBJECTS : selected_in
    ENROLLMENT_SUBJECTS ||--o| GRADES : graded_as
    PROGRAMS ||--o{ CLASS_SCHEDULES : schedules
    SUBJECTS ||--o{ CLASS_SCHEDULES : offered_as

    USERS {
        int id PK
        string student_number UK
        string email UK
        string password_hash
        string role "student | registrar"
        string first_name
        string last_name
        boolean is_active
    }
    PROGRAMS {
        int id PK
        string code UK
        string name
        string department
        int duration_years
        boolean is_active
    }
    PROGRAM_MAJORS {
        int id PK
        int program_id FK
        string name
        boolean is_active
    }
    SUBJECTS {
        int id PK
        int program_id FK
        int major_id FK
        string code
        string title
        decimal units
        int year_level
        string semester
        boolean is_active
    }
    ENROLLMENTS {
        int id PK
        int student_id FK
        int program_id FK
        int major_id FK
        string school_year
        string semester
        int year_level
        string student_type
        string status "submitted | under_review | approved | rejected | withdrawn"
        text registrar_note
    }
    ENROLLMENT_SUBJECTS {
        int enrollment_id PK, FK
        int subject_id PK, FK
    }
    GRADES {
        int enrollment_id PK, FK
        int subject_id PK, FK
        decimal midterm
        decimal final_term
        decimal final_grade
        string remarks "Passed | Failed | Incomplete | In progress"
        int encoded_by FK
    }
    CLASS_SCHEDULES {
        int id PK
        int program_id FK
        int subject_id FK
        string school_year
        string semester
        string section
        string instructor_name
        string days
        time start_time
        time end_time
        string room
        int capacity
    }
```

---

## 4. REST API Documentation (v1)

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/catalog/programs` | Retrieve all active degree programs | Public / Authenticated |
| `GET` | `/api/v1/catalog/subjects` | Filter curriculum subjects by program, year, or term | Public / Authenticated |
| `GET` | `/api/v1/students` | List student directory and enrollment metrics | Registrar Role |
| `GET` | `/api/v1/enrollments` | List enrollment applications with status filters | Authenticated |
| `GET` | `/api/v1/grades` | Query grades by student, term, or academic year | Authenticated |
| `PATCH`| `/api/v1/grades/{enrollment}/{subject}` | Encode/update midterm and final term marks | Registrar Role |

---

## 5. Automated Testing & Verification

The test suite is built on PHPUnit and executable via Laravel Artisan:
```sh
php artisan test
```

### Test Results
- **`RoleAccessTest`**:
  - `test_guest_is_sent_to_login_before_opening_student_workspace` (PASS)
  - `test_student_cannot_open_registrar_tools` (PASS)
  - `test_registrar_cannot_open_student_workspace` (PASS)
- **`GradeCalculationTest`**:
  - `test_grade_averaging_midterm_and_final` (PASS) — Asserts `(1.75 + 1.25) / 2 = 1.50` yields `Passed`.
  - `test_failing_grade_sets_remark_to_failed` (PASS) — Asserts `(3.50 + 3.25) / 2 = 3.38` yields `Failed`.

**Summary**: 5 tests, 15 assertions, 100% pass rate in ~700ms.
