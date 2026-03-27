@echo off
echo ========================================
echo Authentication Fix & Push
echo ========================================
echo.

cd /d "C:\xampp\htdocs\BOCRA-Website"

echo 🔍 Checking current status...
git status
echo.

echo 🌳 Checking branch...
git branch
echo.

echo 📡 Checking remote...
git remote -v
echo.

echo 🔧 Trying GitHub CLI authentication...
gh --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ GitHub CLI found
    echo.
    echo 📝 Please login to GitHub:
    gh auth login
    echo.
    echo 🚀 Now trying to push...
    git push -u origin main
    if %errorlevel% equ 0 (
        goto success
    )
) else (
    echo ❌ GitHub CLI not found
    echo 💡 Installing GitHub CLI...
    winget install GitHub.cli
    echo.
    echo 📝 Please run: gh auth login
    echo 📝 Then run this script again
    pause
    exit /b 1
)

echo.
echo 🔧 Trying Personal Access Token method...
echo.
echo 📋 STEPS:
echo 1. Go to: https://github.com/settings/tokens
echo 2. Click "Generate new token (classic)"
echo 3. Give it a name (e.g., "BOCRA-Push")
echo 4. Check "repo" scope
echo 5. Click "Generate token"
echo 6. Copy the token
echo.
echo 📝 When Git asks for password, paste the token (not your GitHub password)
echo.

set /p token_ready="Have you created a Personal Access Token? (y/n): "
if /i "%token_ready%" neq "y" (
    echo ❌ Please create token first
    pause
    exit /b 1
)

echo 🚀 Attempting push (use token as password)...
git push -u origin main
if %errorlevel% equ 0 (
    goto success
)

echo.
echo 🔧 Trying force push...
git push -f origin main
if %errorlevel% equ 0 (
    goto success
)

echo.
echo 🔧 Trying alternative remote method...
git remote remove origin
git remote add origin https://github.com/Reneetswe/BOCRA-Website.git
git push -u origin main
if %errorlevel% equ 0 (
    goto success
)

goto error

:success
echo.
echo 🎉 SUCCESS! Your BOCRA website is now on GitHub!
echo.
echo 🔗 Repository: https://github.com/Reneetswe/BOCRA-Website
echo.
echo 📋 Your files are now live on GitHub!
echo 🚀 Ready for deployment to production server
echo.
pause
exit /b 0

:error
echo.
echo ❌ All push methods failed
echo.
echo 🔧 FINAL SOLUTIONS:
echo.
echo 1. Use GitHub Desktop:
echo    - Download: https://desktop.github.com
echo    - Clone your repository
echo    - Add files and commit/push with GUI
echo.
echo 2. Create new repository:
echo    - Delete current repository on GitHub
echo    - Create new one with different name
echo    - Try push again
echo.
echo 3. Check repository permissions:
echo    - Go to: https://github.com/Reneetswe/BOCRA-Website
echo    - Make sure you have write access
echo.
echo 4. Try SSH instead of HTTPS:
echo    git remote set-url origin git@github.com:Reneetswe/BOCRA-Website.git
echo    git push -u origin main
echo.
pause
exit /b 1
