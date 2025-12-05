# Database Restructuring Complete - Summary

## Overview
Successfully restructured the class scheduling database from a single `daily_data` table to a normalized two-table structure: `schedules_daily_data` and `assigned_daily_data`.

---

## New Database Structure

### Table 1: `schedules_daily_data`
**Purpose:** Store basic schedule information (what, when, where)

| Column | Type | Description |
|--------|------|-------------|
| `id` | PK | Primary key |
| `date` | date | Class date |
| `day` | string | Day of week (Friday, Monday, etc.) |
| `time` | time | Class time (PHT) |
| `duration` | int | Class duration in minutes (default: 25) |
| `school` | string | School/Account name |
| `class` | string | Class name |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Record update time |

**Indexes:**
- Primary key on `id`
- Index on `date`
- Index on `school, class`
- Unique constraint on `school, class, date, time`

---

### Table 2: `assigned_daily_data`
**Purpose:** Store assignment and status details for each schedule

| Column | Type | Description |
|--------|------|-------------|
| `id` | PK | Primary key |
| `schedule_daily_data_id` | FK | Foreign key to schedules_daily_data |
| `class_status` | enum | active/cancelled (default: active) |
| `main_tutor` | FK nullable | Primary tutor assignment (tutor_id) |
| `backup_tutor` | FK nullable | Backup tutor assignment (tutor_id) |
| `assigned_supervisor` | string nullable | Assigned supervisor supID |
| `finalized_at` | timestamp nullable | When finalized |
| `finalized_by` | FK nullable | Supervisor who finalized (supervisor_id) |
| `cancelled_at` | timestamp nullable | When cancelled |
| `notes` | text nullable | Additional notes/cancellation reason |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Record update time |

**Foreign Keys:**
- `schedule_daily_data_id` → `schedules_daily_data.id` (cascade delete)
- `main_tutor` → `tutor.tutor_id` (set null on delete)
- `backup_tutor` → `tutor.tutor_id` (set null on delete)
- `finalized_by` → `supervisor.supervisor_id` (set null on delete)

---

## Models Created

### 1. `ScheduleDailyData` Model
**File:** `app/Models/ScheduleDailyData.php`

**Relationships:**
- `assignedData()` - hasOne AssignedDailyData
- `tutorAssignments()` - hasMany TutorAssignment (legacy)
- `scheduleHistory()` - hasMany ScheduleHistory
- `supervisorWatches()` - hasMany SupervisorWatch

**Accessors:**
- `class_status` - Gets status from assigned_data
- `main_tutor` - Gets main tutor from assigned_data
- `backup_tutor` - Gets backup tutor from assigned_data
- `assigned_supervisor` - Gets assigned supervisor
- `is_finalized` - Checks if finalized
- `assignment_status` - unassigned/partial/fully_assigned

**Scopes:**
- `forDate($date)` - Filter by specific date
- `forSchool($school)` - Filter by school
- `active()` - Only active classes
- `cancelled()` - Only cancelled classes

---

### 2. `AssignedDailyData` Model
**File:** `app/Models/AssignedDailyData.php`

**Relationships:**
- `schedule()` - belongsTo ScheduleDailyData
- `mainTutor()` - belongsTo Tutor
- `backupTutor()` - belongsTo Tutor
- `finalizedBySupervisor()` - belongsTo Supervisor
- `assignedSupervisorModel()` - belongsTo Supervisor (by supID)

**Methods:**
- `isFinalized()` - Check if finalized
- `isCancelled()` - Check if cancelled
- `finalize($supervisorId)` - Mark as finalized
- `cancel($reason)` - Cancel the assignment

**Scopes:**
- `active()` - Only active assignments
- `cancelled()` - Only cancelled assignments
- `finalized()` - Only finalized assignments

---

### 3. `DailyData` Model (Backward Compatibility)
**File:** `app/Models/DailyData.php`

- Now extends `ScheduleDailyData` for backward compatibility
- Maintains all existing relationships and methods
- Marked as `@deprecated` - use `ScheduleDailyData` instead

---

## Migration Details

**File:** `database/migrations/2025_12_05_100000_restructure_daily_data_to_schedules.php`

**What it does:**
1. Drops foreign key constraints from dependent tables (tutor_assignments, schedule_history, supervisor_watches)
2. Creates `schedules_daily_data` table
3. Creates `assigned_daily_data` table
4. Migrates all existing data from `daily_data`:
   - Basic schedule info → `schedules_daily_data`
   - Assignment details → `assigned_daily_data`
   - Main tutor extracted from `tutor_assignments`
5. Updates foreign keys in dependent tables to point to new `schedules_daily_data.id`
6. Drops old `daily_data` table
7. Re-creates foreign key constraints on dependent tables

**Data Migration Results:**
- ✅ 6 schedules migrated to `schedules_daily_data`
- ✅ 6 assignment records created in `assigned_daily_data`
- ✅ Foreign keys updated in tutor_assignments, schedule_history, supervisor_watches

---

## Updated Controllers

### ScheduleController
**File:** `app/Http/Controllers/ScheduleController.php`

- Added imports for `ScheduleDailyData` and `AssignedDailyData`
- Kept `DailyData` import for backward compatibility during transition
- All queries now work with new table structure via model inheritance

### ImportController
**File:** `app/Http/Controllers/ImportController.php`

**Excel Upload Changes:**
- Now creates records in `schedules_daily_data` (basic schedule info)
- Automatically creates corresponding record in `assigned_daily_data`
- Time stored in PHT (converted from JST by subtracting 1 hour)
- Default `class_status` set to 'active'
- Maintains duplicate detection and validation logic

---

## Benefits of New Structure

### 1. Normalized Data
- Schedule information (what, when, where) separated from assignment details (who, status)
- Reduces data redundancy
- Easier to maintain data integrity

### 2. Clearer Separation of Concerns
- **schedules_daily_data:** Immutable schedule facts
- **assigned_daily_data:** Mutable assignment details

### 3. Support for Multiple Tutors
- Now has dedicated `main_tutor` and `backup_tutor` fields
- Easier to implement tutor backup/replacement logic

### 4. Better Status Tracking
- Clear distinction between class status (active/cancelled) and finalization status
- Dedicated fields for finalization and cancellation timestamps

### 5. Flexible Notes System
- Generic `notes` field can store cancellation reasons, special instructions, etc.

---

## Testing Status

✅ **Migration completed successfully**
- All tables created with correct structure
- Data migrated from old table
- Foreign keys updated correctly

✅ **Models working correctly**
- ScheduleDailyData model queries successfully
- Relationships loading properly
- Accessors providing correct data

✅ **Excel import updated**
- Now creates records in both new tables
- Time conversion working (JST to PHT)

🔄 **Remaining Tasks:**
1. Update ScheduleController queries to explicitly use new table structure
2. Update blade views to display data from `assigned_daily_data`
3. Update any additional controllers that reference `DailyData`
4. Test end-to-end class scheduling workflow
5. Update supervisor assignment logic to use `assigned_daily_data`

---

## Next Steps

1. **Update Class Scheduling Views:**
   - Modify table displays to use `assignedData` relationship
   - Update modals to save to correct tables
   - Test assign supervisor functionality

2. **Update ScheduleController Methods:**
   - Replace `DailyData` references with `ScheduleDailyData`
   - Update queries to use new relationships
   - Test all scheduling operations

3. **Test Workflow:**
   - Excel upload → Create schedules
   - Assign tutors → Update `assigned_daily_data`
   - Assign supervisors → Update `assigned_daily_data`
   - Finalize schedules → Update `finalized_at` and `finalized_by`
   - Cancel classes → Update `class_status` and `cancelled_at`

---

## Rollback Instructions

If needed to rollback:

```bash
php artisan migrate:rollback --step=1
```

This will:
- Recreate the old `daily_data` table
- Drop `assigned_daily_data` table
- Drop `schedules_daily_data` table

---

**Migration Date:** December 5, 2025
**Version:** 2.0 - Database Restructuring
