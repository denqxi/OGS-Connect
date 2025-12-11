# Schedule Cancellation System Documentation

## Overview
A comprehensive emergency cancellation system that handles tutor schedule cancellations with automatic backup promotion and payment blocking capabilities.

## Features

### 1. **Emergency Cancellation Workflow**
- Tutors can cancel schedules in case of emergencies
- Supervisors can cancel schedules on behalf of tutors
- Backup tutors are automatically promoted to main tutor
- Original tutor's payment is automatically blocked
- All affected parties are notified

### 2. **Payment Blocking**
- Cancelled schedules trigger automatic payment blocking
- Work details are marked as `payment_blocked = true`
- Block reason is recorded for audit trail
- Blocked payments won't be included in payroll calculations

### 3. **Backup Tutor Promotion**
- If backup tutor exists: automatically promoted to main tutor
- If no backup: schedule marked as unassigned, requires urgent reassignment
- Backup tutor receives promotion notification
- Supervisor receives status update notification

### 4. **Notification System**
- **Cancelling Tutor**: Receives notification about cancellation and payment block
- **Backup Tutor**: Receives notification about promotion (if applicable)
- **Supervisor**: Receives alert about cancellation and current status

---

## Database Structure

### New Table: `schedule_cancellations`
```sql
- id (bigint, primary key)
- assignment_id (foreign key to assigned_daily_data)
- schedule_id (foreign key to schedules_daily_data)
- original_main_tutor (foreign key to tutors.tutorID)
- backup_tutor_activated (boolean) - whether backup was promoted
- cancellation_reason (text) - detailed reason for cancellation
- cancelled_by (enum: 'main_tutor', 'supervisor')
- cancelled_by_id (bigint) - ID of person who cancelled
- cancelled_at (timestamp) - when cancellation occurred
- timestamps
```

### Modified Tables

#### `assigned_daily_data`
- Added `is_cancelled` (boolean) - marks assignment as cancelled
- Added `cancellation_id` (foreign key to schedule_cancellations)

#### `tutor_work_details`
- Added `payment_blocked` (boolean) - prevents payment processing
- Added `block_reason` (text) - reason for payment block

---

## Implementation Details

### Controllers

#### `ScheduleCancellationController`

**Methods:**

1. **`cancel(Request $request, $assignmentId)`**
   - Validates cancellation reason
   - Creates cancellation record
   - Blocks payment for original tutor
   - Promotes backup tutor if available
   - Updates assignment status
   - Sends notifications to all parties
   - Uses database transactions for data integrity

2. **`assignNewBackup(Request $request, $assignmentId)`**
   - Assigns new backup tutor to cancelled schedules
   - Notifies new backup tutor
   - Updates assignment status to fully_assigned

### Routes

```php
// Supervisor & Tutor Access
Route::post('/schedules/cancel/{assignment}', [ScheduleCancellationController::class, 'cancel']);
Route::post('/schedules/assign-backup/{assignment}', [ScheduleCancellationController::class, 'assignNewBackup']);
```

### Models

#### `ScheduleCancellation`
- Relationships: `assignment()`, `schedule()`, `originalMainTutor()`
- Casts: `backup_tutor_activated` as boolean, `cancelled_at` as datetime

---

## User Interface

### Supervisor Interface

**Location:** `resources/views/schedules/tabs/class-scheduling.blade.php`

1. **Schedule Details Modal**
   - "Cancel Schedule" button (visible for assigned schedules)
   - Triggers cancellation modal

2. **Cancel Schedule Modal**
   - Red warning banner explaining consequences
   - Schedule information display
   - Cancellation reason textarea (required)
   - Acknowledgment of actions
   - Confirmation buttons

**JavaScript Functions:**
- `handleCancelSchedule()` - Gets data from current schedule
- `openCancelScheduleModal(assignmentId, scheduleData)` - Opens modal
- `closeCancelScheduleModal()` - Closes modal
- Form submission handler with loading states

### Tutor Interface

**Location:** `resources/views/tutor/tabs/work_details.blade.php` + `public/js/tutor-work.js`

1. **Work Detail Modal**
   - "Emergency Cancel" button (visible for accepted but not approved schedules)
   - Prominent warning about payment blocking

2. **Emergency Cancellation Modal**
   - Critical warning banner
   - Payment block acknowledgment
   - Schedule details
   - Emergency reason textarea (required)
   - Acknowledgment checkbox (required)
   - Confirmation buttons

**JavaScript Functions:**
- `openTutorEmergencyCancelModal(assignmentId, scheduleData)` - Opens modal
- Form validation and submission
- Payment block acknowledgment verification

---

## Usage Scenarios

### Scenario 1: Tutor Emergency (With Backup)
1. Tutor clicks "Emergency Cancel" in work details modal
2. Tutor provides emergency reason and acknowledges payment block
3. System:
   - Creates cancellation record
   - Blocks tutor's payment for this class
   - Promotes backup tutor to main tutor
   - Sends notifications to tutor, backup, and supervisor
4. Backup tutor becomes main tutor and can complete the class

### Scenario 2: Tutor Emergency (No Backup)
1. Tutor cancels schedule following same process
2. System:
   - Creates cancellation record
   - Blocks tutor's payment
   - Marks schedule as unassigned
   - Alerts supervisor with urgent reassignment notice
3. Supervisor must manually assign new tutor

### Scenario 3: Supervisor-Initiated Cancellation
1. Supervisor opens schedule details modal
2. Clicks "Cancel Schedule" button
3. Provides cancellation reason
4. System processes cancellation same as tutor-initiated
5. Original tutor receives notification with payment block notice

---

## Important Notes

### Payment Blocking
- **Irreversible**: Once payment is blocked, it requires manual intervention to unblock
- **Audit Trail**: All blocks are logged with reasons
- **Payroll Integration**: Payment system must check `payment_blocked` flag before processing

### Notification Requirements
- Cancellations generate 2-3 notifications depending on backup availability
- Notifications include full context: date, school, class, reason
- Supervisor notifications indicate whether backup was activated

### Data Integrity
- Uses database transactions to ensure atomic operations
- Foreign key constraints maintain referential integrity
- Cancellation records provide complete audit trail

### Future Considerations
- Add cancellation frequency tracking per tutor
- Implement cancellation policy enforcement
- Add manual payment unblock workflow
- Create cancellation analytics dashboard
- Consider grace period for very late cancellations

---

## Testing Checklist

- [ ] Tutor can cancel schedule with emergency reason
- [ ] Payment is blocked for cancelling tutor
- [ ] Backup tutor is promoted when available
- [ ] Schedule marked unassigned when no backup
- [ ] All notifications are sent correctly
- [ ] Cancellation record created with full details
- [ ] Database transactions rollback on error
- [ ] UI prevents submission without acknowledgment
- [ ] Supervisor can cancel on behalf of tutor
- [ ] Cancelled schedules don't appear in payroll
- [ ] Audit trail is complete and accurate

---

## Maintenance

### Database
- Regular cleanup of old cancellation records (older than 2 years)
- Monitor cancellation frequency per tutor
- Audit payment blocks quarterly

### Code
- Review notification templates periodically
- Update cancellation policies as needed
- Monitor error logs for transaction failures

### Documentation
- Keep cancellation policies updated
- Document any manual interventions
- Update this file when making changes

---

## Version History
- **v1.0** (2025-01-10): Initial implementation
  - Emergency cancellation system
  - Payment blocking
  - Backup promotion
  - Notification system
