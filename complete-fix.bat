@echo off
echo ========================================
echo Complete Git Fix - Step by Step
echo ========================================
echo.

cd /d "C:\xampp\htdocs\BOCRA-Website"

echo Step 1: Checking current status...
git status
echo.

echo Step 2: Adding all files...
git add .
echo.

echo Step 3: Creating first commit...
git commit -m "Initial commit: BOCRA Website - Complete Regulatory Authority Management System"
if %errorlevel% neq 0 (
    echo ❌ Commit failed - configuring user...
    git config --global user.email "reneetswe@example.com"
    git config --global user.name "Reneetswe"
    git commit -m "Initial commit: BOCRA Website"
)
echo.

echo Step 4: Checking if commit exists...
git log --oneline
echo.

echo Step 5: Creating main branch...
git branch -M main
echo.

echo Step 6: Pushing to GitHub...
echo 📋 Use your Personal Access Token as password
echo 🔗 Repository: https://github.com/Reneetswe/BOCRA-Website
echo.

git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo 🎉 SUCCESS! Your BOCRA website is now on GitHub!
    echo 🔗 View at: https://github.com/Reneetswe/BOCRA-Website
) else (
    echo.
    echo ❌ Push failed - trying force push...
    git push -f origin main
    if %errorlevel% equ 0 (
        echo 🎉 SUCCESS with force push!
    ) else (
        echo.
        echo 🔧 Manual steps:
        echo 1. Check: git log --oneline
        echo 2. If no commits, run: git commit -m "Initial commit"
        echo 3. Then: git push -u origin main
    )
)

echo.
pause
