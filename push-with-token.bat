@echo off
echo ========================================
echo Push with Personal Access Token
echo ========================================
echo.

cd /d "C:\xampp\htdocs\BOCRA-Website"

echo 🚀 Pushing to GitHub...
echo.
echo 📋 When prompted:
echo    Username: Reneetswe
echo    Password: PASTE YOUR TOKEN HERE
echo.
echo 🔗 Your repository: https://github.com/Reneetswe/BOCRA-Website
echo.

git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo 🎉 SUCCESS! Your BOCRA website is now on GitHub!
    echo.
    echo 🔗 View it at: https://github.com/Reneetswe/BOCRA-Website
    echo.
    echo 📋 All your files are now uploaded:
    echo    ✅ Complete website (50+ files)
    echo    ✅ Admin portals
    echo    ✅ API endpoints
    echo    ✅ Database setup
    echo    ✅ Documentation
    echo.
    echo 🚀 Ready for deployment!
) else (
    echo.
    echo ❌ Push failed. Make sure:
    echo    1. Token has 'repo' permissions
    echo    2. Token is copied correctly
    echo    3. Repository exists on GitHub
    echo.
    echo 🔧 Try: git push -f origin main
)

echo.
pause
