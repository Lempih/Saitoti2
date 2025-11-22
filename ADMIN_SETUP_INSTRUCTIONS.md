# Admin Setup Instructions

## Quick Start Guide

### 1. Login as Administrator

**Default Credentials:**
- **Username:** `administrator`
- **Password:** `admin2024`
- **Login URL:** `http://localhost:8000/login.php`

### 2. Fix Database Migration (IMPORTANT - DO THIS FIRST)

After logging in as admin, you need to run the database migration script to fix foreign key constraints.

**Option A: Via Dashboard**
1. After login, you'll be redirected to the Dashboard
2. Click on **"Fix Database Migration"** button in the Quick Actions section
3. Or go directly to: `http://localhost:8000/migrate_fix_fk.php`

**Option B: Direct URL**
- Open in browser: `http://localhost:8000/migrate_fix_fk.php`
- The script will automatically:
  - Drop foreign key constraints
  - Migrate `roll_number` to `registration_number` in both tables
  - Re-add foreign key constraints with new column names
  - Show you step-by-step progress

### 3. Set Up Course Units Table (Optional but Recommended)

To enable course-specific units/subjects:

1. **Create Course Units Table:**
   - Visit: `http://localhost:8000/create_course_units_table.php`
   - This creates the database table for managing course units

2. **Define Course Units:**
   - Go to Dashboard → System Settings → Manage Course Units
   - Or visit: `http://localhost:8000/manage_course_units.php`
   - Add units for each course (e.g., "Database Systems", "Web Development")

### 4. Admin Capabilities

View all admin capabilities at:
- **URL:** `http://localhost:8000/admin_capabilities.php`
- Or click "Admin Capabilities" in the Dashboard

### 5. Start Managing the System

Now you can:
- ✅ Create courses
- ✅ Register students
- ✅ Enter examination results per course unit
- ✅ View all students and results
- ✅ Manage course units

## Troubleshooting

### Error: "Database migration required"

**Solution:**
1. Make sure you're logged in as admin
2. Visit: `http://localhost:8000/migrate_fix_fk.php`
3. The script will fix the database automatically

### Error: "Course units table not found"

**Solution:**
1. Visit: `http://localhost:8000/create_course_units_table.php`
2. This will create the required table

### Can't Login as Admin

**Check:**
1. Database connection is working
2. Administrators table exists
3. Default credentials are: `administrator` / `admin2024`

If credentials don't work:
- Check the database: `SELECT * FROM administrators;`
- Reset password if needed

## Admin URLs

- **Dashboard:** `http://localhost:8000/dashboard.php`
- **Admin Capabilities:** `http://localhost:8000/admin_capabilities.php`
- **Fix Migration:** `http://localhost:8000/migrate_fix_fk.php`
- **Create Course Units Table:** `http://localhost:8000/create_course_units_table.php`
- **Manage Course Units:** `http://localhost:8000/manage_course_units.php`
- **Clear All Data:** `http://localhost:8000/clear_all_data.php`

## Next Steps

1. ✅ Login as admin
2. ✅ Run database migration (`migrate_fix_fk.php`)
3. ✅ Create course units table (optional)
4. ✅ Define course units for each course
5. ✅ Register students or let them sign up
6. ✅ Enter examination results per course unit

