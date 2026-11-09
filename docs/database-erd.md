# Database relationship diagram

The schema is defined in `database/sql/schema.postgresql.sql`. Core relationships and data-integrity rules are represented below.

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
        string role
        boolean is_active
    }
    PROGRAMS {
        int id PK
        string code UK
        string name
        int duration_years
    }
    PROGRAM_MAJORS {
        int id PK
        int program_id FK
        string name
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
    }
    ENROLLMENTS {
        int id PK
        int student_id FK
        int program_id FK
        int major_id FK
        string school_year
        string semester
        int year_level
        string status
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
        string remarks
        int encoded_by FK
    }
    CLASS_SCHEDULES {
        int id PK
        int program_id FK
        int subject_id FK
        string school_year
        string semester
        string section
        int capacity
    }
```

## Integrity rules worth explaining

- A student can have at most one enrollment per school year and semester (`uq_student_term`).
- An enrollment-subject selection is unique for its enrollment and subject pair.
- A grade references that same enrollment-subject pair, preventing a grade for a subject the student did not select.
- Program, major, subject, enrollment, schedule, and grade links use foreign keys with explicit delete behavior.
- Subject units, year level, semester, enrollment status, grade range, and grade remarks have database checks.
