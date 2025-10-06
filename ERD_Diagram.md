# Entity Relationship Diagram (ERD) - OGS Connect System

## Database Tables and Relationships

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                    USERS                                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│     name (string)                                                               │
│     email (string, unique)                                                      │
│     email_verified_at (timestamp, nullable)                                     │
│     password (string)                                                           │
│     remember_token (string, nullable)                                           │
│     created_at, updated_at (timestamps)                                         │
└─────────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        │ 1:N
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                  SESSIONS                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (string)                                                                 │
│ FK: user_id (bigint, nullable) → users.id                                      │
│     ip_address (string, nullable)                                               │
│     user_agent (text, nullable)                                                 │
│     payload (longtext)                                                          │
│     last_activity (integer)                                                     │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                                   TUTORS                                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: tutorID (string, 20) - Format: OGS-T0001                                   │
│     first_name (string, 50)                                                     │
│     last_name (string, 50)                                                      │
│     email (string, 100, unique)                                                 │
│     phone_number (string, 20, nullable)                                         │
│     tusername (string, 50, unique)                                              │
│     tpassword (string, 255) - hashed                                            │
│     status (enum: active, inactive)                                             │
│     sex (enum: M, F, other, nullable)                                           │
│     remember_token (string, nullable)                                           │
│     created_at, updated_at (timestamps)                                         │
└─────────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        │ 1:1
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               TUTOR_DETAILS                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│ FK: tutor_id (string, 20) → tutors.tutorID                                     │
│     address (text, nullable)                                                    │
│     esl_experience (text, nullable)                                             │
│     work_setup (enum: WFH, WAS, Hybrid, nullable)                              │
│     first_day_teaching (date, nullable)                                         │
│     educational_attainment (enum, nullable)                                     │
│     additional_notes (text, nullable)                                           │
│     created_at, updated_at (timestamps)                                         │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                               TUTOR_ACCOUNTS                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│ FK: tutor_id (string, 20) → tutors.tutorID                                     │
│     account_name (string) - 'GLS', 'Babilala', etc.                            │
│     gls_id (string, nullable) - GLS numeric ID                                 │
│     account_number (string, nullable)                                           │
│     username (string, nullable)                                                 │
│     screen_name (string, nullable)                                              │
│     available_days (json, nullable)                                             │
│     available_times (json, nullable)                                            │
│     preferred_time_range (enum, default: flexible)                              │
│     timezone (string, 10, default: UTC)                                         │
│     availability_notes (text, nullable)                                         │
│     restricted_start_time (time, nullable)                                      │
│     restricted_end_time (time, nullable)                                        │
│     company_notes (text, nullable)                                              │
│     status (enum: active, inactive, pending)                                    │
│     created_at, updated_at (timestamps)                                         │
│                                                                                 │
│ UNIQUE: (tutor_id, account_name)                                               │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                                SUPERVISORS                                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: supID (string, 20) - Format: OGS-S0001                                     │
│     sfname (string, 50)                                                         │
│     smname (string, 50, nullable)                                               │
│     slname (string, 50)                                                         │
│     birth_date (date, nullable)                                                 │
│     semail (string, 100, unique)                                                │
│     sconNum (string, 20, nullable)                                              │
│     assigned_account (string, 100, nullable)                                    │
│     srole (string, 50, nullable)                                                │
│     saddress (string, 500, nullable)                                            │
│     steams (string, 100, nullable)                                              │
│     sshift (string, 100, nullable)                                              │
│     status (enum: active, inactive)                                             │
│     password (string)                                                           │
│     remember_token (string, nullable)                                           │
│     created_at, updated_at (timestamps)                                         │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                               DAILY_DATA                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│     school (string)                                                             │
│     class (string)                                                              │
│     duration (integer, default: 25)                                             │
│     date (date)                                                                 │
│     time_jst (time)                                                             │
│     number_required (integer)                                                   │
│     schedule_status (enum: draft, tentative, finalized)                         │
│     class_status (enum: active, cancelled)                                      │
│     cancelled_at (timestamp, nullable)                                          │
│     cancellation_reason (text, nullable)                                        │
│     finalized_at (timestamp, nullable)                                          │
│     finalized_by (string, nullable)                                             │
│     assigned_supervisor (string, nullable) → supervisors.supID                  │
│     assigned_at (timestamp, nullable)                                           │
│     created_at, updated_at (timestamps)                                         │
│                                                                                 │
│ UNIQUE: (school, class, date, time_jst)                                        │
└─────────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        │ 1:N
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            TUTOR_ASSIGNMENTS                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│ FK: daily_data_id (bigint) → daily_data.id                                     │
│ FK: tutor_id (string, 20) → tutors.tutorID                                     │
│     is_backup (boolean, default: false)                                        │
│     was_promoted_from_backup (boolean, default: false)                         │
│     replaced_tutor_name (string, nullable)                                      │
│     promoted_at (timestamp, nullable)                                           │
│     assigned_at (timestamp, nullable)                                           │
│     similarity_score (decimal, 5,4, nullable)                                  │
│     status (enum: assigned, confirmed, cancelled)                               │
│     created_at, updated_at (timestamps)                                         │
│                                                                                 │
│ UNIQUE: (daily_data_id, tutor_id)                                              │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                            SCHEDULE_HISTORY                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│ FK: class_id (bigint) → daily_data.id                                          │
│     class_name (string)                                                         │
│     school (string)                                                             │
│     class_date (date)                                                           │
│     class_time (time)                                                           │
│     status (enum: draft, tentative, finalized, cancelled)                       │
│     action (string) - 'created', 'updated', 'cancelled', etc.                  │
│     performed_by (string, nullable)                                             │
│     reason (text, nullable)                                                     │
│     old_data (json, nullable)                                                   │
│     new_data (json, nullable)                                                   │
│     created_at, updated_at (timestamps)                                         │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                      EMPLOYEE_PAYMENT_INFORMATION                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│     employee_id (string) - Can be tutorID or supID                             │
│     employee_type (enum: tutor, supervisor)                                     │
│     payment_method (string)                                                     │
│     bank_name (string, nullable)                                                │
│     account_number (string, nullable)                                           │
│     account_name (string, nullable)                                             │
│     paypal_email (string, nullable)                                             │
│     gcash_number (string, nullable)                                             │
│     paymaya_number (string, nullable)                                           │
│     hourly_rate (decimal, 10,2, nullable)                                       │
│     monthly_salary (decimal, 10,2, nullable)                                    │
│     payment_frequency (enum: hourly, daily, weekly, monthly)                    │
│     notes (text, nullable)                                                      │
│     is_active (boolean, default: true)                                          │
│     created_at, updated_at (timestamps)                                         │
│                                                                                 │
│ UNIQUE: (employee_id, employee_type)                                           │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                            SECURITY_QUESTIONS                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: id (bigint, auto-increment)                                                │
│     user_type (enum: tutor, supervisor)                                         │
│     user_id (string) - Can be tutorID or supID                                 │
│     question (text)                                                             │
│     answer_hash (string) - hashed answer                                        │
│     created_at, updated_at (timestamps)                                         │
│                                                                                 │
│ INDEX: (user_type, user_id) - Allows multiple questions per user               │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                          PASSWORD_RESET_TOKENS                                 │
├─────────────────────────────────────────────────────────────────────────────────┤
│ PK: email (string)                                                              │
│     token (string)                                                              │
│     created_at (timestamp, nullable)                                            │
└─────────────────────────────────────────────────────────────────────────────────┘
```

## Visual Relationship Diagram

```
USERS (1) ──────────────── (N) SESSIONS

TUTORS (1) ──────────────── (N) TUTOR_ACCOUNTS
TUTORS (1) ──────────────── (1) TUTOR_DETAILS
TUTORS (1) ──────────────── (N) TUTOR_ASSIGNMENTS
TUTORS (1) ──────────────── (N) SECURITY_QUESTIONS
TUTORS (1) ──────────────── (1) EMPLOYEE_PAYMENT_INFORMATION

SUPERVISORS (1) ──────────── (N) DAILY_DATA (assigned_supervisor)
SUPERVISORS (1) ──────────── (N) SECURITY_QUESTIONS
SUPERVISORS (1) ──────────── (1) EMPLOYEE_PAYMENT_INFORMATION

DAILY_DATA (1) ──────────── (N) TUTOR_ASSIGNMENTS
DAILY_DATA (1) ──────────── (N) SCHEDULE_HISTORY

TUTORS (N) ──────────────── (N) DAILY_DATA (via TUTOR_ASSIGNMENTS)
```

## Key Relationships Summary

### One-to-One Relationships:
- **Tutors** ↔ **TutorDetails** (1:1)
- **Tutors** ↔ **EmployeePaymentInformation** (1:1, via employee_id + employee_type)
- **Supervisors** ↔ **EmployeePaymentInformation** (1:1, via employee_id + employee_type)

### One-to-Many Relationships:
- **Users** → **Sessions** (1:N)
- **Tutors** → **TutorAccounts** (1:N)
- **Tutors** → **TutorAssignments** (1:N)
- **Tutors** → **SecurityQuestions** (1:N, via user_id + user_type)
- **Supervisors** → **SecurityQuestions** (1:N, via user_id + user_type)
- **DailyData** → **TutorAssignments** (1:N)
- **DailyData** → **ScheduleHistory** (1:N)
- **Supervisors** → **DailyData** (assigned_supervisor, 1:N)

### Many-to-Many Relationships:
- **Tutors** ↔ **DailyData** (via TutorAssignments)

### Polymorphic Relationships:
- **EmployeePaymentInformation** (polymorphic to Tutors or Supervisors)
- **SecurityQuestions** (polymorphic to Tutors or Supervisors)

## Business Rules and Relationship Labels

### **One-to-One (1:1) Relationships**

#### **Tutors ↔ TutorDetails**
- **Business Rule**: Each tutor must have exactly one detail record
- **Constraint**: `UNIQUE(tutor_id)` in tutor_details table
- **Label**: "Has Profile" / "Belongs To"
- **Rule**: Tutor details are created automatically when tutor is created

#### **Tutors ↔ EmployeePaymentInformation**
- **Business Rule**: Each tutor can have exactly one payment information record
- **Constraint**: `UNIQUE(employee_id, employee_type)` where employee_type = 'tutor'
- **Label**: "Has Payment Info" / "Payment Info For"
- **Rule**: Payment info is optional but unique per tutor

#### **Supervisors ↔ EmployeePaymentInformation**
- **Business Rule**: Each supervisor can have exactly one payment information record
- **Constraint**: `UNIQUE(employee_id, employee_type)` where employee_type = 'supervisor'
- **Label**: "Has Payment Info" / "Payment Info For"
- **Rule**: Payment info is optional but unique per supervisor

### **One-to-Many (1:N) Relationships**

#### **Users → Sessions**
- **Business Rule**: A user can have multiple active sessions (different devices/browsers)
- **Label**: "Has Sessions" / "Session For"
- **Rule**: Sessions are automatically managed by Laravel

#### **Tutors → TutorAccounts**
- **Business Rule**: A tutor can work for multiple companies (GLS, Babilala, etc.)
- **Constraint**: `UNIQUE(tutor_id, account_name)` - one record per tutor per account
- **Label**: "Works For" / "Has Tutors"
- **Rule**: Each account has different availability settings and requirements

#### **Tutors → TutorAssignments**
- **Business Rule**: A tutor can be assigned to multiple classes
- **Label**: "Assigned To" / "Has Assignments"
- **Rule**: Includes backup assignments and promotion tracking

#### **Tutors → SecurityQuestions**
- **Business Rule**: A tutor can have multiple security questions for password recovery
- **Label**: "Has Security Questions" / "Security Question For"
- **Rule**: Questions are used for password reset verification

#### **Supervisors → SecurityQuestions**
- **Business Rule**: A supervisor can have multiple security questions for password recovery
- **Label**: "Has Security Questions" / "Security Question For"
- **Rule**: Questions are used for password reset verification

#### **Supervisors → DailyData (assigned_supervisor)**
- **Business Rule**: A supervisor can be assigned to manage multiple class schedules
- **Label**: "Manages" / "Managed By"
- **Rule**: Only one supervisor per class, but supervisor can manage multiple classes

#### **DailyData → TutorAssignments**
- **Business Rule**: A class can have multiple tutor assignments (including backups)
- **Constraint**: `UNIQUE(daily_data_id, tutor_id)` - no duplicate assignments
- **Label**: "Requires Tutors" / "Assigned To Class"
- **Rule**: Number of assignments should match number_required field

#### **DailyData → ScheduleHistory**
- **Business Rule**: Every change to a class schedule is tracked
- **Label**: "Has History" / "History For"
- **Rule**: Audit trail for all schedule modifications

### **Many-to-Many (N:N) Relationships**

#### **Tutors ↔ DailyData (via TutorAssignments)**
- **Business Rule**: Tutors can be assigned to multiple classes, classes can have multiple tutors
- **Junction Table**: TutorAssignments
- **Label**: "Teaches" / "Taught By"
- **Rule**: Includes backup assignments and similarity scoring for optimal matching

### **Polymorphic Relationships**

#### **EmployeePaymentInformation**
- **Business Rule**: Payment information can belong to either tutors or supervisors
- **Polymorphic Key**: `employee_id` + `employee_type`
- **Label**: "Payment Info For" / "Has Payment Info"
- **Rule**: Single table handles payment for both user types

#### **SecurityQuestions**
- **Business Rule**: Security questions can belong to either tutors or supervisors
- **Polymorphic Key**: `user_id` + `user_type`
- **Label**: "Security Question For" / "Has Security Questions"
- **Rule**: Single table handles security questions for both user types

## **Additional Business Rules**

### **Data Integrity Rules**
1. **Tutor ID Format**: Must follow pattern 'OGS-T' + 4-digit number (e.g., OGS-T0001)
2. **Supervisor ID Format**: Must follow pattern 'OGS-S' + 4-digit number (e.g., OGS-S0001)
3. **Class Uniqueness**: No duplicate classes (same school, class, date, time)
4. **Assignment Limits**: Cannot exceed number_required for each class
5. **Status Workflow**: draft → tentative → finalized (cannot go backwards)

### **Operational Rules**
1. **Account Management**: Tutors can have multiple accounts with different availability
2. **Backup System**: Backup tutors can be promoted to primary assignments
3. **Schedule Ownership**: Only assigned supervisor can modify finalized schedules
4. **Payment Tracking**: Payment info must be active for payroll processing
5. **Security**: Multiple security questions provide better password recovery options

### **Validation Rules**
1. **Email Uniqueness**: No duplicate emails across tutors and supervisors
2. **Username Uniqueness**: Tutor usernames must be unique
3. **Time Validation**: Class times must be within business hours
4. **Availability Matching**: Tutor assignments must match their available times
5. **Account Restrictions**: Company-specific time slot validations apply
