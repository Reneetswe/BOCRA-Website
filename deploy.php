<?php
/**
 * BOCRA Website Deployment Script
 * Run this script to deploy the application to production
 */

// Prevent execution from web browser in production
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

echo "🚀 BOCRA Website Deployment Script\n";
echo "====================================\n\n";

// Check PHP version
echo "📋 Checking PHP version...\n";
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "❌ PHP version " . PHP_VERSION . " is not supported. Requires PHP 7.4 or higher.\n";
    exit(1);
}
echo "✅ PHP version " . PHP_VERSION . " is supported.\n\n";

// Check required extensions
echo "📋 Checking required PHP extensions...\n";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'curl', 'gd'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        echo "❌ Extension '$ext' is missing.\n";
        exit(1);
    }
    echo "✅ Extension '$ext' is loaded.\n";
}
echo "\n";

// Check file permissions
echo "📋 Checking file permissions...\n";
$directories = ['assets', 'backend', 'api', 'storage'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        echo "⚠️  Directory '$dir' does not exist. Creating...\n";
        mkdir($dir, 0755, true);
    }
    if (!is_writable($dir)) {
        echo "❌ Directory '$dir' is not writable.\n";
        exit(1);
    }
    echo "✅ Directory '$dir' is writable.\n";
}
echo "\n";

// Create necessary directories
echo "📋 Creating required directories...\n";
$create_dirs = [
    'storage/logs',
    'storage/uploads',
    'storage/cache',
    'storage/sessions'
];

foreach ($create_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created directory: $dir\n";
    } else {
        echo "✅ Directory exists: $dir\n";
    }
}
echo "\n";

// Update configuration for production
echo "📋 Updating configuration for production...\n";
if (file_exists('backend/config.production.php')) {
    echo "✅ Production configuration file exists.\n";
    echo "⚠️  Remember to update database credentials in backend/config.production.php\n";
} else {
    echo "❌ Production configuration file not found.\n";
    exit(1);
}
echo "\n";

// Create .env file template
echo "📋 Creating .env template...\n";
$env_template = "# BOCRA Website Environment Configuration\n";
$env_template .= "# Copy this file to .env and update the values\n\n";
$env_template .= "DB_HOST=localhost\n";
$env_template .= "DB_NAME=bocra_system\n";
$env_template .= "DB_USER=bocra_user\n";
$env_template .= "DB_PASS=CHANGE_THIS_PASSWORD\n";
$env_template .= "BASE_URL=https://bocra.org.bw\n";
$env_template .= "SESSION_LIFETIME=7200\n";
$env_template .= "ENVIRONMENT=production\n";

if (!file_exists('.env')) {
    file_put_contents('.env', $env_template);
    echo "✅ Created .env template file.\n";
} else {
    echo "✅ .env file already exists.\n";
}
echo "\n";

// Optimize assets
echo "📋 Optimizing assets...\n";
if (file_exists('assets/css')) {
    // Minify CSS files (basic)
    $css_files = glob('assets/css/*.css');
    foreach ($css_files as $file) {
        $css = file_get_contents($file);
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        file_put_contents($file, $css);
        echo "✅ Optimized: $file\n";
    }
}
echo "\n";

// Create deployment checklist
echo "📋 Creating deployment checklist...\n";
$checklist = "# BOCRA Website Deployment Checklist\n\n";
$checklist .= "## Pre-Deployment\n";
$checklist .= "- [ ] Update database credentials in backend/config.production.php\n";
$checklist .= "- [ ] Update BASE_URL in backend/config.production.php\n";
$checklist .= "- [ ] Create database and run schema.sql\n";
$checklist .= "- [ ] Create database user with proper permissions\n";
$checklist .= "- [ ] Copy .env to .env and update values\n";
$checklist .= "- [ ] Set up SSL certificate\n";
$checklist .= "- [ ] Configure web server (Apache/Nginx)\n";
$checklist .= "- [ ] Test all API endpoints\n";
$checklist .= "- [ ] Test login functionality\n";
$checklist .= "- [ ] Test file uploads\n";
$checklist .= "- [ ] Test email functionality\n\n";
$checklist .= "## Post-Deployment\n";
$checklist .= "- [ ] Monitor error logs\n";
$checklist .= "- [ ] Test all user flows\n";
$checklist .= "- [ ] Verify SSL certificate\n";
$checklist .= "- [ ] Check CORS headers\n";
$checklist .= "- [ ] Test mobile responsiveness\n";
$checklist .= "- [ ] Backup database\n";
$checklist .= "- [ ] Set up monitoring\n";
$checklist .= "- [ ] Set up backups\n\n";
$checklist .= "## Security\n";
$checklist .= "- [ ] Change default passwords\n";
$checklist .= "- [ ] Review file permissions\n";
$checklist .= "- [ ] Test XSS protection\n";
$checklist .= "- [ ] Test SQL injection protection\n";
$checklist .= "- [ ] Verify CSRF protection\n";
$checklist .= "- [ ] Test rate limiting\n";

file_put_contents('DEPLOYMENT-CHECKLIST.md', $checklist);
echo "✅ Created DEPLOYMENT-CHECKLIST.md\n\n";

// Create gitignore
echo "📋 Creating .gitignore...\n";
$gitignore = "# BOCRA Website .gitignore\n\n";
$gitignore .= "# Environment files\n";
$gitignore .= ".env\n";
$gitignore .= "backend/config.local.php\n\n";
$gitignore .= "# Logs\n";
$gitignore .= "storage/logs/*.log\n";
$gitignore .= "*.log\n\n";
$gitignore .= "# Uploads\n";
$gitignore .= "storage/uploads/*\n";
$gitignore .= "!storage/uploads/.gitkeep\n\n";
$gitignore .= "# Cache\n";
$gitignore .= "storage/cache/*\n";
$gitignore .= "!storage/cache/.gitkeep\n\n";
$gitignore .= "# Sessions\n";
$gitignore .= "storage/sessions/*\n";
$gitignore .= "!storage/sessions/.gitkeep\n\n";
$gitignore .= "# Composer\n";
$gitignore .= "backend/vendor/\n";
$gitignore .= "backend/composer.lock\n\n";
$gitignore .= "# IDE\n";
$gitignore .= ".vscode/\n";
$gitignore .= ".idea/\n";
$gitignore .= "*.swp\n";
$gitignore .= "*.swo\n\n";
$gitignore .= "# OS\n";
$gitignore .= ".DS_Store\n";
$gitignore .= "Thumbs.db\n\n";
$gitignore .= "# Temporary files\n";
$gitignore .= "*.tmp\n";
$gitignore .= "*.temp\n\n";
$gitignore .= "# Database backups\n";
$gitignore .= "*.sql.backup\n";
$gitignore .= "*.sql.gz\n";

file_put_contents('.gitignore', $gitignore);
echo "✅ Created .gitignore\n\n";

// Create maintenance mode file
echo "📋 Creating maintenance mode files...\n";
$maintenance_html = '<!DOCTYPE html>
<html>
<head>
    <title>BOCRA - Maintenance Mode</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        .logo { font-size: 48px; color: #006B5E; margin-bottom: 20px; }
        .message { color: #666; font-size: 18px; }
    </style>
</head>
<body>
    <div class="logo">BOCRA</div>
    <div class="message">
        <h1>Under Maintenance</h1>
        <p>We are currently performing scheduled maintenance.</p>
        <p>We should be back online shortly. Thank you for your patience.</p>
        <p><small>If you need immediate assistance, please call +267 395-7755</small></p>
    </div>
</body>
</html>';

file_put_contents('maintenance.html', $maintenance_html);
echo "✅ Created maintenance.html\n\n";

echo "🎉 Deployment preparation complete!\n\n";
echo "📋 Next Steps:\n";
echo "1. Review DEPLOYMENT-CHECKLIST.md\n";
echo "2. Update configuration files\n";
echo "3. Set up database\n";
echo "4. Deploy to production server\n";
echo "5. Run tests\n\n";
echo "✅ Ready for deployment!\n";
?>
