# Academic Results Management System

A comprehensive web-based application designed to streamline the management and distribution of student examination results in educational institutions.

## Overview

This Academic Results Management System provides a secure and efficient platform for administrators to manage academic records while offering students easy access to their examination results. The system features robust result tracking, automated grade calculation, and comprehensive reporting capabilities.

## Key Features

### Administrator Features

- **Control Panel Dashboard** - Overview of system statistics and quick access to all features
- **Course Management** - Create and manage academic courses
- **Student Registration** - Register new students and manage student records
- **Results Management** - Enter, update, and delete examination results
- **Secure Authentication** - Protected administrator access with session management

### Student Features

- **Result Inquiry** - Students can view their results by entering their course and roll number
- **Print Functionality** - Download and print result sheets
- **User-Friendly Interface** - Simple and intuitive result viewing experience

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Server**: XAMPP/WAMP/LAMP

## Installation Instructions

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache Web Server
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Steps

1. **Clone or Download the Repository**
   ```bash
   git clone <repository-url>
   cd Student-Result-Management-System
   ```

2. **Database Setup**
   - Import the database schema from `database/academic_results_db.sql`
   - Create a new database named `academic_results_db`
   - Import the SQL file using phpMyAdmin or MySQL command line

3. **Configuration**
   - Open `db_config.php` and update database credentials if needed:
     ```php
     $db_host = "localhost";
     $db_user = "root";
     $db_pass = "";
     $db_name = "academic_results_db";
     ```

4. **Default Administrator Credentials**
   - Username: `administrator`
   - Password: `admin2024`
   - **Important**: Change the default password after first login

5. **Server Configuration**
   - Place the project folder in your web server directory (htdocs for XAMPP, www for WAMP)
   - Access the application via: `http://localhost/Student-Result-Management-System/`

## Database Schema

The system uses the following main tables:

- **administrators** - Stores administrator login credentials
- **courses** - Contains course/class information
- **student_records** - Student registration data
- **exam_results** - Examination results and grades

## Security Features

- Prepared statements to prevent SQL injection
- Session-based authentication
- Input validation and sanitization
- Password protection for administrator access

## Usage Guide

### For Administrators

1. **Login**: Access the administrator login page and enter credentials
2. **Dashboard**: View system statistics and navigate to different sections
3. **Add Course**: Create new academic courses with unique IDs
4. **Register Students**: Add student records with name, roll number, and course
5. **Enter Results**: Input examination marks for registered students
6. **Manage Data**: Update or delete courses, students, and results as needed

### For Students

1. **Access Portal**: Go to the login page
2. **Select Course**: Choose your enrolled course from the dropdown
3. **Enter Roll Number**: Input your roll number
4. **View Results**: See your examination results with detailed marks and percentage
5. **Print**: Use the print button to save or print your result sheet

## File Structure

```
Student-Result-Management-System/
├── database/
│   └── academic_results_db.sql    # Database schema
├── css/
│   ├── home.css                   # Dashboard styling
│   ├── login.css                   # Login page styling
│   ├── form.css                    # Form styling
│   ├── manage.css                  # Management page styling
│   └── student.css                 # Student result page styling
├── js/
│   └── main.js                     # JavaScript functions
├── images/                         # System images and logos
├── db_config.php                   # Database configuration
├── auth_check.php                  # Authentication handler
├── login.php                       # Administrator login
├── dashboard.php                   # Control panel
├── add_classes.php                 # Create courses
├── add_students.php                # Register students
├── add_results.php                 # Enter results
├── manage_classes.php              # View courses
├── manage_students.php             # View students
├── manage_results.php              # Update/delete results
├── student.php                     # Student result viewer
└── index.html                      # Homepage
```

## Customization

The system is designed to be easily customizable:

- **Styling**: Modify CSS files in the `css/` directory
- **Database**: Update table structures in the SQL file
- **Functionality**: Extend PHP files with additional features
- **Branding**: Update logos and images in the `images/` directory

## Browser Compatibility

- Google Chrome (Recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari
- Opera

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `db_config.php`
   - Ensure MySQL service is running
   - Check database name matches the imported schema

2. **Session Issues**
   - Clear browser cookies and cache
   - Ensure PHP session directory has write permissions

3. **Page Not Found**
   - Verify file paths are correct
   - Check Apache mod_rewrite is enabled (if using URL rewriting)

## Future Enhancements

- Email result notifications
- PDF result generation
- Advanced reporting and analytics
- Multi-admin support with role-based access
- Mobile-responsive design improvements
- Result export functionality

## License

This project is open-source and available for educational and commercial use.

## Support

For issues, questions, or contributions, please refer to the project repository or contact the development team.

---

**Note**: This system is designed for educational purposes. Ensure proper security measures are in place before deploying to production environments.
