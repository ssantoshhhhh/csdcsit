# SRKR Attendance Portal

A comprehensive web-based attendance management system for SRKR Engineering College, designed to track and manage student attendance across different sections and years.

## 🎯 Project Overview

The SRKR Attendance Portal is a PHP-based web application that provides a complete solution for managing student attendance records. The system supports multiple user roles including faculty members, HOD (Head of Department), and students, with different access levels and functionalities.

## ✨ Features

### 📊 Core Features
- **Multi-Section Support**: Manage attendance for different sections (CSIT-A, CSIT-B, CSD) and years (2nd, 3rd, 4th year)
- **Real-time Attendance Tracking**: Faculty can mark attendance for students in real-time
- **Attendance History**: View detailed attendance records and statistics
- **Student Search**: Quick search functionality to find specific students
- **Attendance Reports**: Generate and view attendance reports by section and date

### 👥 User Roles

#### Faculty Portal
- Secure login with attendance code
- Mark attendance for students
- View attendance records
- Generate attendance reports
- Modify attendance entries (with approval system)

#### HOD Portal
- Administrative access to all sections
- View comprehensive attendance statistics
- Approve attendance modifications
- Access detailed reports and analytics

#### Student Portal
- View individual attendance records
- Check attendance percentage
- Access attendance history

### 📈 Additional Features
- **Leaderboard System**: Track and display attendance performance
- **Modification System**: Request and approve attendance corrections
- **Responsive Design**: Mobile-friendly interface
- **Modern UI**: Clean and intuitive user interface

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Bootstrap 5
- **Icons**: Font Awesome
- **Server**: Apache/Nginx (XAMPP/WAMP/LAMP)

## 📋 Prerequisites

Before running this application, ensure you have:

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache/Nginx web server
- XAMPP, WAMP, or LAMP stack (recommended)

## 🚀 Installation

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/srkr-attendance-portal.git
cd srkr-attendance-portal
```

### Step 2: Database Setup
1. Create a new MySQL database named `attendance`
2. Import the database schema (SQL files will be provided)
3. Configure database connection in `connect.php`

### Step 3: Configure Database Connection
1. Rename `connect.php.dummy` to `connect.php`
2. Update the database credentials in `connect.php`:
```php
$conn = mysqli_connect('localhost', 'your_username', 'your_password', 'attendance');
$sconn = new mysqli('localhost', 'your_username', 'your_password', 'attendance');
```

### Step 4: Web Server Configuration
1. Place the project in your web server's document root
2. Ensure the web server has read/write permissions for the project directory
3. Configure your web server to serve PHP files

### Step 5: Access the Application
Open your web browser and navigate to:
```
http://localhost/attendance/
```

## 🔐 Security Configuration

### Default Credentials
- **Faculty Login**: Use the attendance code provided by your administrator
- **HOD Login**: 
  - Username: `hod`
  - Password: `hod123`

⚠️ **Important**: Change these default credentials in production!

### Security Recommendations
1. Change all default passwords immediately after installation
2. Use HTTPS in production environments
3. Regularly update PHP and MySQL versions
4. Implement proper session management
5. Use prepared statements for all database queries
6. Enable error logging and monitoring

## 📁 Project Structure

```
attendance/
├── index.php                 # Main landing page
├── connect.php              # Database connection (gitignored)
├── connect.php.dummy        # Database connection template
├── login.php              # Unified login for all users
├── logout.php             # Unified logout for all users
├── attendance_entry.php   # Attendance marking interface
├── hod_dashboard.php      # HOD dashboard
├── student_attendance.php # Student attendance view
├── attendance_leaderboard.php # Attendance leaderboard
├── attendance_modifications.php # Attendance modification system
├── nav.php                 # Navigation component
├── nav_top.php            # Top navigation
├── head.php               # Header component
├── footer.php             # Footer component
├── .gitignore            # Git ignore file
└── README.md             # This file
```

## 🎮 Usage Guide

### For Faculty
1. Navigate to the faculty login page
2. Enter your attendance code
3. Select the section you want to manage
4. Mark attendance for students
5. Submit attendance records

### For HOD
1. Login with HOD credentials
2. Access the HOD dashboard
3. View attendance statistics
4. Approve attendance modifications
5. Generate comprehensive reports

### For Students
1. Select your section from the main page
2. Search for your name or roll number
3. View your attendance records and statistics

## 🔧 Configuration

### Database Tables
The system uses the following main tables:
- `28csit_a_attendance` - 2nd Year CSIT-A Section
- `28csit_b_attendance` - 2nd Year CSIT-B Section
- `28csd_attendance` - 2nd Year CSD Section
- `27csit_attendance` - 3rd Year CSIT Section
- `27csd_attendance` - 3rd Year CSD Section
- `26csd_attendance` - 4th Year CSD Section
- `modifications` - Attendance modification requests

### Customization
- Modify section configurations in `index.php`
- Update styling in CSS files
- Customize database schema as needed
- Add new user roles if required

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `connect.php`
   - Ensure MySQL service is running
   - Check database name and permissions

2. **Session Issues**
   - Ensure PHP sessions are enabled
   - Check file permissions for session storage

3. **Page Not Found**
   - Verify web server configuration
   - Check file paths and permissions
   - Ensure .htaccess is properly configured

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the development team
- Check the documentation for common solutions

## 🔄 Version History

- **v1.0.0** - Initial release with basic attendance tracking
- **v1.1.0** - Added HOD portal and modification system
- **v1.2.0** - Enhanced UI and added leaderboard feature

---

**Developed for SRKR Engineering College** 🎓

*This attendance portal is designed to streamline the attendance management process and provide comprehensive tracking and reporting capabilities for educational institutions.* 