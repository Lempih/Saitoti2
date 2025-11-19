# Migration Guide: Database Update

This guide will help you migrate from the old database structure to the new one.

## Database Changes

### Old Database: `srms`
### New Database: `academic_results_db`

## Table Name Changes

| Old Table Name | New Table Name |
|---------------|----------------|
| `admin_login` | `administrators` |
| `class` | `courses` |
| `students` | `student_records` |
| `result` | `exam_results` |

## Column Name Changes

### administrators (formerly admin_login)
- `userid` → `admin_username`
- `password` → `admin_password`
- Added: `created_at` (timestamp)

### courses (formerly class)
- `name` → `course_name`
- `id` → `course_id`
- Added: `created_at` (timestamp)

### student_records (formerly students)
- `name` → `full_name`
- `rno` → `roll_number`
- `class_name` → `enrolled_course`
- Added: `registration_date` (timestamp)

### exam_results (formerly result)
- `name` → `student_name`
- `rno` → `roll_number`
- `class` → `course_name`
- `p1` → `subject_1`
- `p2` → `subject_2`
- `p3` → `subject_3`
- `p4` → `subject_4`
- `p5` → `subject_5`
- `marks` → `total_marks`
- `percentage` → `grade_percentage`
- Added: `created_at`, `updated_at` (timestamps)

## Migration Steps

1. **Backup your existing database**
   ```sql
   mysqldump -u root -p srms > backup_srms.sql
   ```

2. **Create new database**
   ```sql
   CREATE DATABASE academic_results_db;
   ```

3. **Import new schema**
   - Use phpMyAdmin or MySQL command line
   - Import `database/academic_results_db.sql`

4. **Migrate data (if needed)**
   If you have existing data, you'll need to write migration queries to transfer data from old tables to new tables.

5. **Update configuration**
   - The system now uses `db_config.php` for database configuration
   - Update credentials if needed

## Default Credentials

- **Username**: `administrator`
- **Password**: `admin2024`

**Important**: Change the default password immediately after first login!

## Code Changes

All PHP files have been updated to use:
- Prepared statements for security
- New table and column names
- Improved error handling
- Better code structure

## File Structure Changes

- `init.php` → Now includes `db_config.php` for backward compatibility
- `session.php` → Now includes `auth_check.php` for backward compatibility
- New file: `db_config.php` - Main database configuration
- New file: `auth_check.php` - Authentication handler
- New file: `js/main.js` - JavaScript functions

## Testing Checklist

After migration, test the following:

- [ ] Administrator login
- [ ] Create new course
- [ ] Register new student
- [ ] Enter examination results
- [ ] View student results
- [ ] Update results
- [ ] Delete results
- [ ] Print results

## Rollback

If you need to rollback:
1. Restore from backup
2. Revert to old codebase
3. Update database connection settings

## Support

If you encounter any issues during migration, check:
1. Database connection settings in `db_config.php`
2. PHP error logs
3. MySQL error logs
4. Browser console for JavaScript errors

