# 📚 Library Management System

A comprehensive, web-based library management system built with **PHP, MySQL, HTML, CSS, and JavaScript**. This system provides complete functionality for managing library operations including user management, book cataloging, circulation, fines, and reporting.

## ✨ Features

### 1. User Management
- **User Registration & Authentication**: Secure login/logout with password hashing
- **Role-Based Access Control**: Admin, Librarian, and Member/Student roles
- **User Profiles**: Manage personal information, contact details
- **Account Status Management**: Active/Suspended user status
- **User Dashboard**: Role-specific dashboards for each user type

### 2. Book & Resource Management
- **CRUD Operations**: Add, edit, update, delete books and resources
- **ISBN Management**: Unique ISBN tracking and management
- **Author Management**: Centralized author database with search
- **Publisher Management**: Track publishers and their details
- **Book Categories**: Classifications using Dewey Decimal and Library of Congress systems
- **Book Status Tracking**: Available, Borrowed, Reserved, Lost, Damaged
- **Physical Location**: Track shelf numbers, sections, and rows

### 3. Catalog Management
- **Digital Catalog**: Complete online book catalog
- **Metadata Storage**: Comprehensive book information
- **Advanced Search**: Search by title, ISBN, author, category
- **Filtering & Sorting**: Multiple filter and sort options
- **Barcode System**: Generate and manage barcodes for books

### 4. Search & Discovery
- **Full-Text Search**: Search across titles and descriptions
- **Advanced Filtering**: By author, category, year, status
- **Intelligent Sorting**: A-Z, newest, most popular
- **Search History**: Remember previous searches

### 5. Circulation Management
- **Borrowing System**: Easy book borrowing with due dates
- **Returning System**: Book return tracking with condition assessment
- **Renewal System**: Allow book renewals with limits
- **Due Date Tracking**: Automatic due date calculation
- **Overdue Detection**: Automatic identification and alerts

### 6. Reservation System
- **Book Reservations**: Reserve unavailable books
- **Queue Management**: Automated queue for waiting users
- **Hold Period**: Configurable hold period for reserved books
- **Ready Notifications**: Notify users when books are available

### 7. Fine & Payment System
- **Automatic Fine Calculation**: Late fees, damage fees, lost book fees
- **Payment Tracking**: Record payments with receipts
- **Fine Status Management**: Pending, paid, waived status
- **Receipt Generation**: Digital receipts for payments
- **Balance Management**: Track user balance and outstanding fines

### 8. Notification System
- **Due Date Reminders**: Alerts before due dates
- **Overdue Alerts**: Notification system for overdue books
- **Reservation Notifications**: Alert when reserved books are ready
- **Fine Notifications**: Payment reminders
- **System Announcements**: Global system messages

### 9. Inventory Management
- **Stock Tracking**: Monitor book quantities
- **Condition Tracking**: Track book condition over time
- **Missing Book Detection**: Identify missing books
- **Audit System**: Inventory audit trails and reports

### 10. Reports & Analytics
- **Borrowing Reports**: Detailed borrowing statistics
- **Overdue Reports**: List of overdue books and users
- **User Activity Reports**: Individual and aggregate user activity
- **Popular Books Report**: Most borrowed books
- **Fine Reports**: Fine collection and outstanding amounts
- **Inventory Reports**: Stock and condition reports
- **Dashboard Statistics**: Real-time system statistics

### 11. System Configuration
- **Loan Rules**: Configurable lending periods and limits
- **Fine Rates**: Adjustable daily fine rates
- **User Policies**: Borrowing limits and restrictions
- **Access Control Rules**: Role-based permissions

### 12. Staff Management
- **Librarian Accounts**: Create and manage librarian accounts
- **Role Permissions**: Granular permission control
- **Activity Logs**: Track staff actions and changes
- **Audit Trail**: Complete audit logging

### 13. Security
- **Secure Authentication**: Password hashing with bcrypt
- **Session Management**: Secure session handling
- **SQL Injection Prevention**: Prepared statements
- **Access Control**: Role-based access restrictions
- **Audit Logging**: Complete system audit trail
- **Data Validation**: Input validation and sanitization

### 14. Mobile Responsive Design
- **Responsive Layout**: Works on desktop, tablet, and mobile
- **Mobile-Optimized Interface**: Touch-friendly controls
- **Progressive Enhancement**: Graceful degradation

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache, Nginx, etc.)
- Modern web browser

### Installation

1. **Clone or Download** the project to your web server directory:
```bash
cp -r library-management /var/www/html/
```

2. **Create Database**:
   - Import the SQL schema from `database/library_schema.sql`
   - Or run it directly in phpMyAdmin

3. **Configure Database Connection**:
   - Edit `config/config.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'library_management');
   ```

4. **Set Permissions**:
   - Make `logs/` directory writable
   - Make `assets/uploads/` directory writable (if needed)

5. **Access the Application**:
   - Navigate to `http://localhost/library-management/`
   - Default login: Create an admin account via registration

### Default Admin Setup

1. Register at `/register.php` as first user (will be admin)
2. Log in with credentials
3. Create other users from Admin Dashboard

## 📁 Project Structure

```
library-management/
├── admin/                      # Admin dashboard pages
│   ├── dashboard.php
│   ├── users.php
│   ├── books.php
│   ├── fines.php
│   └── reports.php
├── librarian/                  # Librarian dashboard pages
│   ├── dashboard.php
│   ├── catalog.php
│   ├── circulation.php
│   ├── returns.php
│   └── fines.php
├── member/                     # Member/Student portal
│   ├── dashboard.php
│   ├── catalog.php
│   ├── my-books.php
│   ├── my-fines.php
│   └── profile.php
├── api/                        # API endpoints
│   ├── search-books.php
│   ├── borrow-book.php
│   ├── return-book.php
│   ├── reserve-book.php
│   └── get-notifications.php
├── classes/                    # PHP classes
│   ├── Database.php            # Database connection
│   ├── User.php                # User management
│   ├── Book.php                # Book management
│   ├── BorrowRecord.php        # Circulation management
│   ├── Fine.php                # Fine management
│   ├── Reservation.php         # Reservation management
│   ├── Notification.php        # Notification system
│   ├── Report.php              # Reporting & Analytics
│   └── Metadata.php            # Category/Author/Publisher
├── config/                     # Configuration files
│   └── config.php
├── database/                   # Database schema
│   └── library_schema.sql
├── assets/                     # Assets
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── logs/                       # Application logs
├── includes/                   # Helper functions (future)
├── index.php                   # Login page
├── register.php                # Registration page
├── dashboard.php               # Dashboard router
└── logout.php                  # Logout handler
```

## 👥 User Roles

### Admin
- Manage all users (create, edit, suspend)
- Manage all books
- View reports and statistics
- Configure system settings
- View audit logs
- Manage fines and payments

### Librarian
- Browse and manage book catalog
- Process book borrowing
- Process book returns
- Manage book reservations
- Collect fines and payments
- View circulation reports

### Member/Student
- Browse book catalog
- Borrow and return books
- Reserve books
- Renew borrowed books
- View personal borrowing history
- Pay fines online
- View personal profile

## 🗄️ Database Schema

### Main Tables:
- `users` - User accounts and profiles
- `books` - Book catalog
- `authors` - Author information
- `publishers` - Publisher details
- `categories` - Book categories
- `book_authors` - Book-Author relationships
- `borrow_records` - Circulation tracking
- `reservations` - Book reservations
- `fines` - Fine records
- `payments` - Payment transactions
- `notifications` - User notifications
- `system_logs` - Audit trail
- `inventory_audit` - Inventory records
- `barcodes` - Barcode tracking

## 🔧 Configuration

Edit `config/config.php` to customize:

```php
// Fine Configuration
define('DAILY_FINE_RATE', 5);           // Per day fee
define('MAX_BORROW_DURATION', 14);      // Days to borrow
define('MAX_BOOKS_PER_USER', 5);        // Max books per user
define('DAMAGE_FEE', 50);               // Damage fee percentage
define('LOST_BOOK_FEE_PERCENT', 200);   // Lost book fee percentage

// Session & Security
define('SESSION_TIMEOUT', 3600);        // 1 hour
define('PASSWORD_MIN_LENGTH', 8);       // Minimum password length
```

## 🔐 Security Features

- ✅ Password hashing with bcrypt
- ✅ Prepared SQL statements (SQL injection prevention)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Input validation and sanitization
- ✅ CSRF protection ready
- ✅ Audit logging system
- ✅ Secure error handling

## 📊 Reports Available

1. **Borrowing Reports** - Track lending patterns
2. **Overdue Reports** - Monitor overdue items and users
3. **User Activity Reports** - Track user behavior
4. **Popular Books** - Most checked out books
5. **Fine Reports** - Collection analysis
6. **Inventory Reports** - Stock status and condition
7. **Financial Reports** - Revenue and outstanding amounts

## 🌐 API Endpoints

### Search Books
```
GET /api/search-books.php?q=query
```

### Notifications
```
GET /api/get-notifications.php
GET /api/get-notifications.php?count=1
```

### Borrow Book
```
POST /api/borrow-book.php
Parameters: book_id
```

### Return Book
```
POST /api/return-book.php
Parameters: borrow_id, condition
```

### Reserve Book
```
POST /api/reserve-book.php
Parameters: book_id
```

## 📱 Mobile Features

- Responsive design for all screen sizes
- Touch-friendly interface
- Mobile-optimized tables and forms
- Simplified navigation on mobile
- Readable fonts and spacing

## 🛠️ Maintenance

### Regular Tasks:
```bash
# Clear old logs (monthly)
rm -rf logs/*.log

# Database backup
mysqldump -u user -p library_management > backup.sql

# Check audit logs
SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 100;
```

### Updating Fines:
Overdue fines are calculated automatically when books are returned or checked.

## 📈 Future Enhancements

- Email/SMS notification integration
- RFID system integration
- Payment gateway integration (Stripe, PayPal)
- Mobile app (React Native)
- Advanced search with Elasticsearch
- QR code generation
- Automated fine calculation scheduler
- Book recommendation system
- Interlibrary loan system

## 🐛 Troubleshooting

### Database Connection Error
- Check database credentials in `config/config.php`
- Verify MySQL is running
- Check database exists

### Permission Errors
- Ensure `logs/` directory is writable (`chmod 755`)
- Ensure `assets/uploads/` is writable

### Session Issues
- Clear browser cookies
- Check session timeout settings
- Verify PHP session directory permissions

## 📞 Support

For issues or questions:
1. Check the documentation
2. Review error logs in `logs/error.log`
3. Check database audit logs
4. Verify user permissions

## 📝 License

This Library Management System is provided as-is for educational and organizational use.

## 🙏 Contributing

To contribute improvements:
1. Test thoroughly
2. Document changes
3. Follow existing code style
4. Submit feedback

## 📋 Changelog

### Version 1.0.0 (2026-02-28)
- Initial release
- All core features implemented
- Responsive design
- Complete CRUD operations
- Reporting system
- Notification system
- Fine management

---

**Last Updated**: 2026-02-28  
**Version**: 1.0.0  
**Status**: Production Ready
