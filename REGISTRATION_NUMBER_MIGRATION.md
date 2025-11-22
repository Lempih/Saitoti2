# Registration Number Migration Guide

## Overview

The system has been migrated to use **registration numbers** instead of roll numbers. **Registration numbers now act as both the unique identifier and password** for student login.

## What Changed

### Student Login
- **Before:** Email + Password
- **After:** Registration Number only (acts as password)

### Student Signup
- **Before:** Full Name + Email + Roll Number + Password + Confirm Password
- **After:** Full Name + Email + Registration Number (no separate password)

### Key Features
- Registration numbers are unique identifiers across all courses
- Students login using only their registration number
- No password needed - registration number is the password
- Simpler authentication process

## Migration Steps

### 1. Run Database Migration

Navigate to the migration script in your browser:
```
http://localhost:8000/migrate_to_registration_number.php
```

This script will:
- Rename `roll_number` to `registration_number` in `student_records` table
- Rename `roll_number` to `registration_number` in `exam_results` table
- Update foreign key constraints
- Update existing records

### 2. Test Student Login

1. Go to: `http://localhost:8000/student_login.php`
2. Enter a registration number
3. You should be logged in and redirected to the dashboard

### 3. Test Student Signup

1. Go to: `http://localhost:8000/student_signup.php`
2. Fill in:
   - Full Name
   - Email
   - Registration Number (this will be your login credential)
   - Course
3. Submit the form

## Files Updated

### Student Portal
- ✅ `student_login.php` - Updated to use registration_number only
- ✅ `student_dashboard.php` - Updated to display registration_number
- ✅ `student_signup.php` - Updated to use registration_number (no password)
- ✅ `includes/student_nav.php` - Updated navigation links

### Migration Scripts
- ✅ `migrate_to_registration_number.php` - Database migration script

### Still Need Updates
- ⚠️ `add_students.php` - Admin form needs to use registration_number
- ⚠️ `manage_students.php` - Display registration_number
- ⚠️ `add_results.php` - Use registration_number
- ⚠️ `manage_results.php` - Use registration_number
- ⚠️ `student.php` - Result viewer needs to accept registration_number

## How It Works

### Authentication Flow

1. **Student Signs Up:**
   - Provides: Full Name, Email, Registration Number, Course
   - Registration Number is stored in database
   - No password is stored

2. **Student Logs In:**
   - Enters only their Registration Number
   - System checks if Registration Number exists
   - If found, login succeeds
   - Session is created with student data

3. **Access Dashboard:**
   - Student can view their profile
   - See their exam results
   - View/print result sheets

## Security Considerations

- Registration numbers must be unique across the entire system
- Registration numbers act as passwords, so they should be kept secure
- Consider adding email verification for additional security
- Future enhancement: Hash registration numbers if needed

## Troubleshooting

### "System not fully configured" Error
- Run `migrate_to_registration_number.php` first
- Ensure database connection is working

### "Invalid registration number" Error
- Verify the registration number exists in database
- Check if migration was completed successfully
- Ensure no typos in registration number

### Database Errors
- Check if columns were renamed properly
- Verify foreign key constraints
- Run migration script again if needed

## Next Steps

1. ✅ Complete database migration
2. ⚠️ Update all admin forms to use registration_number
3. ⚠️ Update result viewing to use registration_number
4. ⚠️ Test all functionality
5. ⚠️ Update documentation

## Notes

- The old `password` column may still exist but is not used
- The system automatically migrates columns on first use
- Backward compatibility is maintained during transition

