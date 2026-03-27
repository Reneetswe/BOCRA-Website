@echo off
echo ========================================
echo BOCRA Website - GitHub Push Script
echo ========================================
echo.

REM Check if Git is installed
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Git is not installed!
    echo Please download and install Git from: https://git-scm.com/download/win
    pause
    exit /b 1
)

echo ✅ Git is installed
echo.

REM Navigate to project directory
cd /d "C:\xampp\htdocs\BOCRA-Website"
echo ✅ Changed to project directory
echo.

REM Check if this is a git repository
if not exist ".git" (
    echo 📦 Initializing Git repository...
    git init
    if %errorlevel% neq 0 (
        echo ❌ Failed to initialize Git repository
        pause
        exit /b 1
    )
    echo ✅ Git repository initialized
) else (
    echo ✅ Git repository already exists
)
echo.

REM Add all files
echo 📋 Adding all files to Git...
git add .
if %errorlevel% neq 0 (
    echo ❌ Failed to add files
    pause
    exit /b 1
)
echo ✅ Files added successfully
echo.

REM Check if there are changes to commit
git diff --cached --quiet
if %errorlevel% equ 0 (
    echo ℹ️  No changes to commit
    echo.
    echo Trying to push existing commits...
) else (
    REM Commit changes
    echo 💾 Creating commit...
    git commit -m "Initial commit: Complete BOCRA Website with all features

✅ Features Included:
- Complete BOCRA website with responsive design
- Admin portals (BOCRA, Complaints, Cybersecurity, Licensing)
- Registrar portal for domain management
- Public portal for complaints and applications
- Chat widget with AI bot functionality
- Complete API endpoints with CORS support
- Database schema and setup scripts
- Production-ready configuration
- Security headers and SSL ready
- Comprehensive documentation

🔧 Technical Stack:
- PHP 7.4+ with PDO/MySQL
- HTML5/CSS3/JavaScript
- Bootstrap and Font Awesome
- MySQL/MariaDB database
- Apache/Nginx compatible

🚀 Ready for deployment!"
    if %errorlevel% neq 0 (
        echo ❌ Failed to create commit
        pause
        exit /b 1
    )
    echo ✅ Commit created successfully
)
echo.

REM Check if remote exists
git remote get-url origin >nul 2>&1
if %errorlevel% neq 0 (
    echo 🔗 No remote repository found
    echo.
    echo Please create a repository on GitHub first:
    echo 1. Go to https://github.com
    echo 2. Click "New repository"
    echo 3. Name it: BOCRA-Website
    echo 4. Make it Public
    echo 5. Click "Create repository"
    echo.
    set /p github_username="Enter your GitHub username: "
    if "%github_username%"=="" (
        echo ❌ Username cannot be empty
        pause
        exit /b 1
    )
    echo.
    echo 📡 Adding remote repository...
    git remote add origin https://github.com/%github_username%/BOCRA-Website.git
    if %errorlevel% neq 0 (
        echo ❌ Failed to add remote repository
        pause
        exit /b 1
    )
    echo ✅ Remote repository added
) else (
    echo ✅ Remote repository already exists
    git remote -v
)
echo.

REM Set main branch
echo 🌳 Setting main branch...
git branch -M main
if %errorlevel% neq 0 (
    echo ❌ Failed to set main branch
    pause
    exit /b 1
)
echo ✅ Main branch set
echo.

REM Try to push
echo 🚀 Pushing to GitHub...
echo.
echo If you get authentication errors, you may need to:
echo 1. Use GitHub Personal Access Token
echo 2. Configure Git credentials
echo 3. Use GitHub CLI (gh auth login)
echo.

git push -u origin main
if %errorlevel% neq 0 (
    echo.
    echo ❌ Push failed! Common solutions:
    echo.
    echo 1. Authentication Issues:
    echo    - Run: git config --global user.name "Your Name"
    echo    - Run: git config --global user.email "your.email@example.com"
    echo    - Or use GitHub CLI: gh auth login
    echo.
    echo 2. Repository Issues:
    echo    - Make sure repository exists on GitHub
    echo    - Check your GitHub username
    echo    - Ensure you have push access
    echo.
    echo 3. Network Issues:
    echo    - Check internet connection
    echo    - Try: git push -f origin main
    echo.
    pause
    exit /b 1
)

echo.
echo 🎉 SUCCESS! Your BOCRA website is now on GitHub!
echo.
echo 📋 Next Steps:
echo 1. Visit your repository on GitHub
echo 2. Clone to production server when ready
echo 3. Run: php setup-database.php
echo 4. Configure production settings
echo.
echo 📞 For support: Check README-DEPLOYMENT.md
echo.
pause
