# Git Push Troubleshooting Guide

## 🚨 Common Push Refusal Issues & Solutions

### Issue 1: Authentication Failed
**Error**: `Authentication failed for 'https://github.com/...'`

**Solutions:**
```bash
# Method A: Use GitHub CLI (Recommended)
winget install GitHub.cli
gh auth login

# Method B: Use Personal Access Token
# 1. Go to GitHub → Settings → Developer settings → Personal access tokens
# 2. Generate new token with 'repo' permissions
# 3. Use token as password when prompted

# Method C: Configure Git credentials
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### Issue 2: Repository Not Found
**Error**: `Repository not found`

**Solutions:**
1. Verify repository exists on GitHub
2. Check spelling of repository name
3. Ensure you have access to the repository
4. Check your GitHub username in the URL

### Issue 3: Permission Denied
**Error**: `Permission denied (publickey)`

**Solutions:**
```bash
# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "your.email@example.com"

# Add SSH key to GitHub account
# Copy: cat ~/.ssh/id_rsa.pub
# Paste in GitHub → Settings → SSH keys

# Use SSH URL instead of HTTPS
git remote set-url origin git@github.com:YOUR_USERNAME/BOCRA-Website.git
```

### Issue 4: Branch Issues
**Error**: `src refspec main does not match any`

**Solutions:**
```bash
# Create and switch to main branch
git checkout -b main
git add .
git commit -m "Initial commit"
git push -u origin main
```

### Issue 5: Remote Already Exists
**Error**: `fatal: remote origin already exists`

**Solutions:**
```bash
# Remove and re-add remote
git remote remove origin
git remote add origin https://github.com/YOUR_USERNAME/BOCRA-Website.git
```

## 🚀 Quick Fix Commands

### Option 1: Fresh Start
```bash
cd "C:\xampp\htdocs\BOCRA-Website"
rm -rf .git
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/BOCRA-Website.git
git push -u origin main
```

### Option 2: Force Push
```bash
git push -f origin main
```

### Option 3: Alternative Branch
```bash
git checkout -b deploy
git push -u origin deploy
```

## 🔧 Best Practices

1. **Use GitHub CLI** for authentication
2. **Create repository first** on GitHub
3. **Use HTTPS** for simplicity
4. **Check file permissions** on .git folder
5. **Verify internet connection**

## 📱 Alternative Solutions

### GitHub Desktop
1. Download from: https://desktop.github.com
2. Clone repository locally
3. Drag and drop files
4. Commit and push with GUI

### Visual Studio Code
1. Install Git extension
2. Use built-in source control
3. Click commit and push buttons

## 🆘 Emergency Solutions

If nothing works:
1. Create new repository with different name
2. Use ZIP upload on GitHub website
3. Contact GitHub support

---

**Remember: The force-push script I created handles most of these automatically!**
