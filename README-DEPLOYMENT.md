# BOCRA Website - Deployment Guide

## 🚀 Quick Deployment Checklist

### Pre-Deployment Requirements
- ✅ PHP 7.4+ or PHP 8.0+
- ✅ MySQL 5.7+ or MariaDB 10.2+
- ✅ Apache 2.4+ or Nginx 1.18+
- ✅ SSL Certificate (HTTPS required)
- ✅ Composer (for PHP dependencies)

### Database Setup
1. **Create Database**
   ```bash
   mysql -u root -p
   CREATE DATABASE bocra_registry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Run Setup Script**
   ```bash
   php setup-database.php
   ```

3. **Update Production Config**
   ```bash
   cp backend/config.production.php backend/config.php
   # Edit database credentials in backend/config.php
   ```

### File Structure Check
```
BOCRA-Website/
├── .htaccess                    # Apache configuration
├── .gitignore                   # Git ignore file
├── index.html                   # Main homepage
├── about.html                   # About page
├── login.php                    # Login system
├── logout.php                   # Logout
├── chat-widget.php              # Chat widget
├── deploy.php                   # Deployment script
├── setup-database.php           # Database setup
├── DEPLOYMENT-CHECKLIST.md      # Deployment checklist
├── README-DEPLOYMENT.md         # This file
├── backend/
│   ├── config.php               # Main configuration
│   ├── config.production.php    # Production template
│   └── vendor/                  # PHP dependencies
├── api/                         # API endpoints
│   ├── cors-header.php          # CORS headers
│   ├── submit-complaint.php     # Complaint API
│   ├── submit-license-application.php
│   └── ... (other APIs)
├── assets/                      # Static assets
│   ├── images/                  # Images
│   ├── css/                     # Stylesheets
│   └── js/                      # JavaScript
├── bocra/                       # BOCRA admin portal
├── complaints-admin/            # Complaints admin
├── cybersecurity-admin/         # Cybersecurity admin
├── licensing-admin/             # Licensing admin
└── registrar/                   # Registrar portal
```

### CORS Issues Fixed
- ✅ Added `cors-header.php` to all API endpoints
- ✅ Proper preflight request handling
- ✅ Security headers included
- ✅ Content-Type headers set correctly

### Security Configuration
1. **Apache (.htaccess)**
   - Security headers configured
   - CORS headers for API
   - File upload limits set
   - Error logging enabled
   - Directory listing disabled

2. **PHP Configuration**
   - Display errors disabled in production
   - Error logging enabled
   - Session security configured
   - File permissions set correctly

3. **Database Security**
   - Prepared statements used throughout
   - SQL injection protection
   - Input sanitization implemented

### Environment Variables
Create `.env` file:
```bash
cp .env.example .env
# Edit .env with your values
```

### SSL/HTTPS Setup
1. Obtain SSL certificate (Let's Encrypt recommended)
2. Configure Apache/Nginx for HTTPS
3. Redirect HTTP to HTTPS
4. Update BASE_URL in config

### Testing Before Deployment
1. **API Endpoints**
   ```bash
   # Test CORS
   curl -X OPTIONS -H "Origin: *" http://your-domain.com/api/submit-complaint.php
   
   # Test POST
   curl -X POST -H "Content-Type: application/json" \
        -d '{"test":"data"}' \
        http://your-domain.com/api/submit-complaint.php
   ```

2. **Login System**
   - Test admin login: `admin@bocra.org.bw / admin123`
   - Test registrar login: `registrar@test.com / registrar123`
   - Change default passwords immediately

3. **File Uploads**
   - Test document uploads
   - Check file size limits
   - Verify upload permissions

### Common Deployment Issues & Solutions

#### Issue 1: CORS Errors
**Problem**: "No 'Access-Control-Allow-Origin' header is present"
**Solution**: Ensure `cors-header.php` is included in all API files

#### Issue 2: Database Connection
**Problem**: "Connection refused" or "Access denied"
**Solution**: 
- Check database credentials in `backend/config.php`
- Ensure database user has proper permissions
- Verify MySQL/MariaDB is running

#### Issue 3: File Permissions
**Problem**: "Permission denied" errors
**Solution**:
```bash
chmod 755 /path/to/BOCRA-Website
chmod 755 /path/to/BOCRA-Website/storage
chmod 644 /path/to/BOCRA-Website/*.php
```

#### Issue 4: Session Issues
**Problem**: Users getting logged out frequently
**Solution**: Check session save path and permissions

#### Issue 5: Upload Issues
**Problem**: File uploads failing
**Solution**: Check `upload_max_filesize` and `post_max_size` in php.ini

### Production Optimization
1. **Enable Gzip Compression** (already in .htaccess)
2. **Set Browser Caching** (already configured)
3. **Minify CSS/JS** (optional)
4. **Enable PHP OPcache**
5. **Configure Database Caching**

### Monitoring & Maintenance
1. **Error Logs**: `/var/log/bocra_errors.log`
2. **Access Logs**: Server access logs
3. **Database Backups**: Daily backups recommended
4. **SSL Certificate**: Monitor expiry
5. **Security Updates**: Regular updates required

### Performance Considerations
- Database indexing optimized
- API response times monitored
- Image compression implemented
- Caching headers configured
- CDN recommended for static assets

### Backup Strategy
1. **Database**: Daily automated backups
2. **Files**: Weekly full backups
3. **Configuration**: Version control (Git)
4. **Recovery**: Test restoration process

### Support Contact
- Technical: admin@bocra.org.bw
- Emergency: +267 395-7755

### Post-Deployment Checklist
- [ ] All API endpoints responding
- [ ] Login functionality working
- [ ] File uploads working
- [ ] Email notifications working
- [ ] SSL certificate valid
- [ ] Error monitoring configured
- [ ] Backup system active
- [ ] Performance monitoring set up
- [ ] Security scan completed
- [ ] User acceptance testing completed

---

## 🚨 Important Security Notes

1. **Change Default Passwords**: Immediately change all default passwords
2. **Update Database Credentials**: Use strong, unique passwords
3. **Enable HTTPS**: Never deploy without SSL/TLS
4. **Regular Updates**: Keep PHP, MySQL, and dependencies updated
5. **Monitor Logs**: Regularly check error and access logs
6. **Backup Regularly**: Implement automated backup system
7. **Security Audit**: Conduct regular security assessments

---

## 📞 Deployment Support

For deployment issues:
1. Check error logs first
2. Review this documentation
3. Test with the provided scripts
4. Contact technical support if needed

**Deploy with confidence! 🎉**
