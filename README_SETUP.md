# Setup Instructions

## Quick Start

1. **Run the dummy data seeder:**
   ```
   Open in browser: http://localhost:8000/seed_dummy_data.php
   ```
   This will create:
   - 5 Courses
   - 8 Students with login credentials
   - 8 Result records

2. **Test the system:**

   **Admin Login:**
   - Username: `administrator`
   - Password: `admin2024`
   - URL: `http://localhost:8000/login.php`

   **Student Login (Example):**
   - Email: `john.doe@example.com`
   - Password: `password123`
   - Roll Number: `1001`
   - Course: `Computer Science`
   - URL: `http://localhost:8000/student_login.php`

   **Quick Results Check:**
   - Roll Number: `1001`
   - Course: `Computer Science`
   - URL: `http://localhost:8000/login.php` (use Quick Result Check section)

## Test Credentials

### Admin
- **Username:** administrator
- **Password:** admin2024

### Students

| Name | Email | Password | Roll Number | Course |
|------|-------|----------|-------------|--------|
| John Doe | john.doe@example.com | password123 | 1001 | Computer Science |
| Jane Smith | jane.smith@example.com | password123 | 1002 | Computer Science |
| Michael Johnson | michael.j@example.com | password123 | 2001 | Business Administration |
| Sarah Williams | sarah.w@example.com | password123 | 2002 | Business Administration |
| David Brown | david.b@example.com | password123 | 3001 | Engineering |
| Emily Davis | emily.d@example.com | password123 | 3002 | Engineering |
| Robert Wilson | robert.w@example.com | password123 | 4001 | Medicine |
| Lisa Anderson | lisa.a@example.com | password123 | 5001 | Law |

## Features to Test

1. **Admin Dashboard** - View statistics
2. **Course Management** - Add/View courses
3. **Student Management** - Register/View students
4. **Results Management** - Add/Update/Delete results
5. **Student Login** - Students can login and view their results
6. **Quick Results** - Public result viewing without login
7. **Student Signup** - New students can register

## Troubleshooting

### Toast Error Fixed
- The toast.js error has been fixed by ensuring DOM is ready before initialization
- All pages now load toast.js at the end of the body tag

### Students Can't See Results
- Ensure results are added for the student's roll number and course
- Check that the student is registered in the correct course
- Verify the roll number matches exactly

### Database Connection
- Ensure MySQL is running: `brew services start mysql`
- Database name: `academic_results_db`
- Default credentials: root / (empty password)

