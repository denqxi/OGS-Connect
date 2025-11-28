# Screening Modals Documentation

## Overview
This directory contains all modal dialogs used in the hiring & onboarding screening process.

## File Structure

### `edit_mdl.blade.php`
**Purpose**: Contains all HTML structure for screening modals

**Key Features**:
- Clean, organized HTML with clear section comments
- 15+ different modal types for complete screening workflow
- All JavaScript functionality extracted to external file
- Easy to maintain and debug

### JavaScript
**Location**: `public/js/screening-modals.js`

**Functions**:
- Modal show/hide operations
- AJAX data loading
- Form validations
- Error handling
- Username/credential generation
- API communications

## Modal Types

### 1. Edit Screening Modal
- View/edit applicant information
- Update interviewer, account, status
- Manage schedules and notes

### 2. Pass Modal
- Move applicant to next stage
- Select next status
- Schedule demos/training

### 3. Fail Options Modal
- Reschedule missed interviews
- Transfer to different account
- Archive declined/not recommended

### 4. Onboarding Pass/Fail Modal
- Register as tutor
- Generate credentials
- Process onboarding completion

### 5. Confirmation Modals
- Pass confirmation
- Fail confirmation
- Account transfer confirmation
- Registration success

## Usage

### Loading Modal Data
```javascript
loadEditModalData(screeningId);
```

### Opening Onboarding Modal
```javascript
showOnboardingPassFailModal(id, name, account, schedule, email);
```

### Submitting Forms
All form submissions handled via AJAX with proper error handling and validation.

## Controller Integration
**Controller**: `App\Http\Controllers\ApplicationFormController`

**Key Methods**:
- `moveToDemo()` - Create screening record
- `archiveApplication()` - Archive applicant
- `viewTable()` - Load screening list

## Best Practices

1. **Always use section comments** when adding new modals
2. **Extract complex JavaScript** to screening-modals.js
3. **Follow naming conventions**: `show{ModalName}()` / `hide{ModalName}()`
4. **Validate forms** before submission
5. **Handle errors gracefully** with user-friendly messages

## Maintenance Notes

- Keep modal HTML minimal and clean
- Use consistent styling classes (Tailwind)
- Document any new JavaScript functions
- Test all modal interactions after changes
- Clear cache after updates: `php artisan optimize:clear`

## Version
**Current Version**: 1.0.0
**Last Updated**: November 25, 2025
**Author**: OGS Connect Development Team
