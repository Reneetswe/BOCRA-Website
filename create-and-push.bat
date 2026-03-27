@echo off
echo ========================================
echo Create Repository and Push
echo ========================================
echo.

cd /d "C:\xampp\htdocs\BOCRA-Website"

echo 🔍 Checking if we have commits...
git log --oneline -n 1
if %errorlevel% neq 0 (
    echo ❌ No commits found - creating one...
    git add .
    git commit -m "Initial commit: BOCRA Website"
    git config --global user.email "reneetswe@example.com"
    git config --global user.name "Reneetswe"
)

echo.
echo 🌳 Setting main branch...
git branch -M main
echo.

echo 🔗 OPTION 1: Create repository manually
echo    1. Go to: https://github.com/new
echo    2. Name: BOCRA-Website
echo    3. Make it Public
echo    4. Click Create repository
echo    5. Then run: git push -u origin main
echo.

echo 🔗 OPTION 2: Use GitHub CLI to create repo
gh --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ GitHub CLI found - creating repository...
    gh repo create Reneetswe/BOCRA-Website --public --source=. --remote=origin --push
    if %errorlevel% equ 0 (
        goto success
    )
) else (
    echo ❌ GitHub CLI not found
)

echo.
echo 🔗 OPTION 3: Try different repository name
echo    Creating repository with timestamp...
set timestamp=%date:~-4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set timestamp=%timestamp: =0%
git remote remove origin
git remote add origin https://github.com/Reneetswe/BOCRA-Website-%timestamp%
git push -u origin main
if %errorlevel% equ 0 (
    echo ✅ Created repository with timestamp name
    goto success
)

echo.
echo ❌ All automatic methods failed
echo.
echo 📋 MANUAL STEPS:
echo 1. Go to: https://github.com/new
echo 2. Repository name: BOCRA-Website
echo 3. Make it Public
echo 4. Click Create repository
echo 5. Run: git push -u origin main
echo.
pause
exit /b 1

:success
echo.
echo 🎉 SUCCESS! Your BOCRA website is now on GitHub!
echo 📋 Check your GitHub repositories to see it
echo.
pause
exit /b 0
