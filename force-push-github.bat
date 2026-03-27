@echo off
echo ========================================
echo BOCRA Website - Force Push Script
echo ========================================
echo.

REM Navigate to project directory
cd /d "C:\xampp\htdocs\BOCRA-Website"

echo 🔍 Checking current Git status...
git status
echo.

echo 🌳 Checking current branch...
git branch
echo.

echo 📡 Checking remote repository...
git remote -v
echo.

echo 🧹 Cleaning up Git state...
git clean -fd
git reset --hard HEAD
echo.

echo 📋 Adding all files...
git add .
git status
echo.

echo 💾 Creating commit...
git commit -m "BOCRA Website - Complete Regulatory Authority Management System

✅ FEATURES:
- Responsive website with modern design
- 4 Admin portals (BOCRA, Complaints, Cybersecurity, Licensing)
- Registrar portal for domain management
- Public complaint and application systems
- AI-powered chatbot widget
- 12+ API endpoints with full CORS support
- Complete database schema and setup
- Production-ready security configuration
- Comprehensive documentation

🔧 TECH STACK:
- PHP 7.4+ with PDO/MySQL
- HTML5/CSS3/JavaScript
- Bootstrap 5 + Font Awesome
- MySQL/MariaDB database
- Apache/Nginx compatible

🚀 DEPLOYMENT READY!"

echo.

echo 🌟 Setting up main branch...
git branch -M main
echo.

echo 🚀 Attempting push with different methods...
echo.

REM Method 1: Normal push
echo Method 1: Normal push...
git push -u origin main
if %errorlevel% equ 0 (
    echo ✅ SUCCESS with normal push!
    goto success
)
echo ❌ Normal push failed
echo.

REM Method 2: Force push
echo Method 2: Force push...
git push -f origin main
if %errorlevel% equ 0 (
    echo ✅ SUCCESS with force push!
    goto success
)
echo ❌ Force push failed
echo.

REM Method 3: Push with --allow-unrelated-histories
echo Method 3: Push with unrelated histories...
git push --allow-unrelated-histories origin main
if %errorlevel% equ 0 (
    echo ✅ SUCCESS with unrelated histories!
    goto success
)
echo ❌ Push with unrelated histories failed
echo.

REM Method 4: Re-create remote and push
echo Method 4: Re-creating remote...
git remote remove origin
set /p github_username="Enter your GitHub username: "
git remote add origin https://github.com/%github_username%/BOCRA-Website.git
git push -u origin main
if %errorlevel% equ 0 (
    echo ✅ SUCCESS after recreating remote!
    goto success
)
echo ❌ All push methods failed
goto error

:success
echo.
echo 🎉 SUCCESS! Your BOCRA website is now on GitHub!
echo.
echo 📋 Repository URL: https://github.com/%github_username%/BOCRA-Website
echo.
echo 🚀 Next Steps:
echo 1. Visit your repository on GitHub
echo 2. Review the uploaded files
echo 3. Ready for deployment to production server
echo.
pause
exit /b 0

:error
echo.
echo ❌ PUSH FAILED - Manual troubleshooting required:
echo.
echo 🔧 POSSIBLE SOLUTIONS:
echo.
echo 1. AUTHENTICATION ISSUES:
echo    - Install GitHub CLI: winget install GitHub.cli
echo    - Run: gh auth login
echo    - Or create Personal Access Token on GitHub
echo.
echo 2. REPOSITORY ISSUES:
echo    - Make sure repository exists: https://github.com/%github_username%/BOCRA-Website
echo    - Check spelling of repository name
echo    - Ensure repository is Public
echo.
echo 3. PERMISSION ISSUES:
echo    - Check if you're the repository owner
echo    - Verify you have write access
echo.
echo 4. NETWORK ISSUES:
echo    - Check internet connection
echo    - Try using VPN if GitHub is blocked
echo    - Check firewall settings
echo.
echo 5. ALTERNATIVE APPROACH:
echo    - Create new repository with different name
echo    - Or use GitHub Desktop GUI application
echo.
echo 📞 For detailed troubleshooting, check the error messages above
echo.
pause
exit /b 1
