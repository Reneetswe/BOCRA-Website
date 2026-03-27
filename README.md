# BOCRA Website

Botswana Communications Regulatory Authority (BOCRA) official website with Microsoft Authenticator two-factor authentication (2FA).

## Features

- **Professional Government Website Design**: Clean, trustworthy interface suitable for regulatory authority
- **Microsoft Authenticator 2FA**: Time-based One-Time Password (TOTP) authentication using RFC 6238
- **Multi-Step Registration**: 3-step registration process with authenticator setup
- **Two-Stage Login**: Credentials verification followed by 2FA challenge
- **Responsive Design**: Mobile-friendly layout with modern CSS
- **Security Features**: Rate limiting, secure secret handling, server-side verification

## Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB database
- Composer (PHP package manager)
- Web server (Apache/Nginx) with PHP support

### Installation

1. **Install Dependencies**
   ```bash
   composer install
   ```
   This installs the `robthree/twofactorauth` library required for Microsoft Authenticator 2FA support.

2. **Database Setup**
   ```bash
   mysql -u root -p < bocra.sql
   ```
   This creates the database and required tables with sample users.

3. **Web Server Configuration**
   
   Place the files in your web server's document root:
   - For XAMPP: `C:\xampp\htdocs\BOCRA-Website\`
   - For Laragon: `C:\laragon\www\BOCRA-Website\`
   - For other servers: `/var/www/html/BOCRA-Website/`

4. **Database Configuration**
   
   Edit `backend/config/db.php` if needed to match your database credentials:
   ```php
   $host = 'localhost';
   $dbname = 'bocra_website';
   $username = 'root';
   $password = ''; // Your database password
   ```

5. **Access the Website**
   
   Open your browser and navigate to:
   - `http://localhost/BOCRA-Website/` (XAMPP)
   - `http://localhost/BOCRA-Website/` (Laragon)

## File Structure

```
BOCRA-Website/
├── index.html                 # Main landing page
├── login.html                 # Login page with 2FA
├── register.html              # Registration with authenticator setup
├── bocra.sql                  # Database schema and sample data
├── composer.json              # PHP dependencies
├── README.md                  # This file
├── assets/
│   └── images/                # Image assets
├── backend/
│   ├── api/
│   │   └── auth_2fa.php       # 2FA API endpoints
│   └── config/
│       └── db.php             # Database configuration
└── vendor/                    # Composer dependencies (auto-generated)
```

## Authentication Flow

### Registration Process

1. **Step 1**: Basic information (name, email, password)
2. **Step 2**: Account details (organization type, purpose)
3. **Step 3**: Microsoft Authenticator setup
   - Generate TOTP secret server-side
   - Display QR code for easy scanning
   - Manual entry option as fallback
   - Verify 6-digit code to confirm setup

### Login Process

1. **Stage 1**: Email and password verification
2. **Stage 2**: 2FA challenge (if user has authenticator set up)
   - Enter 6-digit code from Microsoft Authenticator
   - 30-second timer with visual countdown
   - Rate limiting (3 failed attempts = 10-minute lockout)

## Security Features

- **Server-Side Secret Generation**: TOTP secrets never exposed to frontend
- **Secure Verification**: All code verification happens server-side
- **Rate Limiting**: Prevents brute force attacks
- **Session Management**: Secure session handling
- **Input Validation**: Comprehensive form validation
- **CSRF Protection**: Built-in security measures

## API Endpoints

### `/backend/api/auth_2fa.php`

- `POST generate_secret`: Generate new TOTP secret and QR code
- `POST verify_code`: Verify 6-digit TOTP code
- `POST save_secret`: Save verified secret to user account
- `POST get_secret`: Retrieve user's TOTP secret for verification

## Sample Users

The database includes sample users for testing:

- **Admin**: `admin@bocra.org.bw` / `admin123`
- **Regular User**: `test@example.com` / `test123`

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Development Notes

- Uses Google Fonts (Forum for headings, Lato for body text)
- No external CSS frameworks (custom CSS implementation)
- Responsive design with mobile-first approach
- Modern JavaScript (ES6+) features
- PHP 7.4+ with PDO for database operations

## Troubleshooting

### Common Issues

1. **Composer Install Fails**
   - Ensure Composer is installed and in your PATH
   - Check PHP version compatibility (7.4+)

2. **Database Connection Errors**
   - Verify MySQL/MariaDB is running
   - Check database credentials in `backend/config/db.php`
   - Ensure database exists and user has permissions

3. **QR Code Not Displaying**
   - Check that `vendor/autoload.php` is accessible
   - Verify GD library is enabled in PHP
   - Check browser console for JavaScript errors

4. **2FA Verification Fails**
   - Ensure phone time is set to automatic
   - Check that Microsoft Authenticator app is up to date
   - Verify the correct account is selected in the app

### Debug Mode

To enable debug mode, add this to `backend/config/db.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## License

© 2026 Botswana Communications Regulatory Authority. All rights reserved.

## Support

For technical support:
- Email: support@bocra.org.bw
- Phone: +267 3957755
