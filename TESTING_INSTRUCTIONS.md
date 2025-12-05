# Testing Instructions - Employee Availability Tab

## Quick Test Checklist

### Step 1: Page Load Test
- [ ] Open browser and navigate to `http://localhost:8000/scheduling`
- [ ] Verify page loads WITHOUT continuous reloading
- [ ] Page should remain stable (no reloads every 500ms)

### Step 2: Tab Navigation Test
- [ ] Click on different tabs (if available)
- [ ] Should NOT trigger infinite reload loop
- [ ] Employee Availability tab should be the default active tab

### Step 3: Tutor List Display Test
- [ ] Verify tutor list displays correctly
- [ ] Check that each row shows:
  - Tutor name (from applicant)
  - Email address
  - Phone number (if available)
  - Available times (GLS schedule)
- [ ] Verify pagination controls work (if more than 5 tutors exist)

### Step 4: Search Functionality Test
- [ ] Type in the search box (e.g., tutor name, email)
- [ ] Page should NOT auto-submit while typing
- [ ] Click the "Apply" button
- [ ] Verify search results are filtered correctly
- [ ] Results should show only matching tutors

### Step 5: Filter Functionality Test

#### Status Filter:
- [ ] Click Status dropdown
- [ ] Select a status (e.g., "active")
- [ ] Page should NOT auto-submit on selection
- [ ] Click "Apply" button
- [ ] Verify tutors are filtered by status

#### Day Filter:
- [ ] Click Day dropdown
- [ ] Select a day (e.g., "Monday")
- [ ] Page should NOT auto-submit on selection
- [ ] Click "Apply" button
- [ ] Verify tutors are filtered by availability on that day

#### Time Slot Filter:
- [ ] Click Time Slot dropdown
- [ ] Select a time range (e.g., "9:00 AM - 11:00 AM")
- [ ] Click "Apply" button
- [ ] Verify tutors are filtered by availability in that time slot

### Step 6: Clear Filters Test
- [ ] Apply some filters
- [ ] Click "Clear" button
- [ ] Verify all filters are reset to default
- [ ] Tutor list should show all active tutors again

### Step 7: Modal/Action Buttons Test (if any)
- [ ] Click on any action buttons for a tutor
- [ ] Verify modals or details load without errors
- [ ] Close modal and verify page remains stable

### Step 8: Console Error Check
- [ ] Open browser Developer Tools (F12)
- [ ] Go to Console tab
- [ ] Look for any JavaScript errors (red icons)
- [ ] Should see NO errors related to:
  - Duplicate event listeners
  - Undefined methods
  - Database column errors
- [ ] Page should not show 404 or 500 errors in Network tab

### Step 9: Database Validation
- [ ] Run: `php artisan tinker`
- [ ] Execute: `Tutor::active()->first()`
- [ ] Verify tutor object loads correctly
- [ ] Execute: `Tutor::active()->first()->applicant`
- [ ] Verify applicant relationship loads with first_name, last_name

## Expected Behavior After Fixes

✅ **Page Stability:** /scheduling loads and remains stable (no auto-reloads)
✅ **Filter Control:** Users must click "Apply" to submit filters
✅ **Search:** Searches on names, emails, phone numbers
✅ **Display:** Shows tutor names from applicant table correctly
✅ **Sorting:** Tutors sorted by first name, then last name
✅ **Availability:** GLS schedule displayed for each tutor
✅ **Pagination:** Shows 5 tutors per page with navigation

## Troubleshooting

### If page still reloads infinitely:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check console for errors (F12 → Console)
4. Verify ScheduleController.php changes are applied
5. Verify employee-availability.js changes are applied

### If search/filters don't work:
1. Check that "Apply" button is being clicked
2. Check Network tab in DevTools for POST requests
3. Verify filter values are being sent in request
4. Check Laravel logs: `tail -f storage/logs/laravel.log`

### If tutor names don't show:
1. Verify Tutor model has relationship to Applicant
2. Verify applicants table has first_name, last_name columns
3. Run: `php artisan tinker`
4. Check: `Tutor::first()->applicant`

### If database errors occur:
1. Check error message in browser console
2. Check Laravel logs for detailed error
3. Verify database migrations are up to date
4. Verify tutor_account table structure (note: singular, not plural)

## Browser Console Commands (for debugging)

Open browser console (F12 → Console) and run:

```javascript
// Check for duplicate event listeners
console.log('Checking page events...');

// Clear any stuck intervals
clearInterval(window.autoSubmitInterval || null);

// Check current URL
console.log('Current URL:', window.location.href);
```

## Contact Support

If issues persist after testing:
1. Check SCHEDULING_FIXES_SUMMARY.md for full technical details
2. Review Laravel logs in storage/logs/
3. Verify all file changes were applied correctly
4. Run: `php artisan config:cache` to refresh configuration
