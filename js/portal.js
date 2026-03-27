// ========================================
// BOCRA LICENSING PORTAL - JAVASCRIPT
// ========================================

// Configuration
const API_BASE = 'http://localhost/bocra-website/backend/api';

// Global State
let selectedAccountType = '';
let userData = {};
let setupOtpBoxes = [];
let verifyOtpBoxes = [];

// ========================================
// INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('Portal initialized');
    initializeApp();
});

async function initializeApp() {
    setupFormListeners();
    setupPasswordStrength();
    setupPasswordMatch();
    setupOtpBoxes();
    
    // Check for existing session
    const token = localStorage.getItem('bocra_website_token');
    if (token) {
        try {
            const response = await fetch(API_BASE + '/me.php', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const json = await response.json();
            if (json.success) {
                userData = json.data.user;
                switchView('dashboard');
            } else {
                localStorage.removeItem('bocra_website_token');
            }
        } catch (error) {
            localStorage.removeItem('bocra_website_token');
        }
    }
}

// ========================================
// TAB SWITCHING
// ========================================
function switchTab(tab) {
    console.log('Switching to tab:', tab);
    
    const tabs = document.querySelectorAll('.tab');
    const forms = document.querySelectorAll('.form-content');
    
    tabs.forEach(t => t.classList.remove('active'));
    forms.forEach(f => f.classList.remove('active'));
    
    // Find and activate the correct tab
    tabs.forEach(t => {
        if (t.textContent.toLowerCase().includes(tab)) {
            t.classList.add('active');
        }
    });
    
    const activeForm = document.getElementById(tab + 'View');
    if (activeForm) {
        activeForm.classList.add('active');
    }
}

// ========================================
// ACCOUNT TYPE SELECTION
// ========================================
function selectAccountType(type) {
    console.log('Account type selected:', type);
    
    const cards = document.querySelectorAll('.account-type-card');
    const companyFields = document.getElementById('companyFields');
    const accountTypeInput = document.getElementById('accountType');
    
    cards.forEach(card => card.classList.remove('selected'));
    const selectedCard = document.querySelector(`[data-type="${type}"]`);
    if (selectedCard) selectedCard.classList.add('selected');
    
    selectedAccountType = type;
    if (accountTypeInput) accountTypeInput.value = type;
    
    if (companyFields) {
        if (type === 'company') {
            companyFields.classList.add('show');
        } else {
            companyFields.classList.remove('show');
        }
    }
    
    if (accountTypeInput) hideError(accountTypeInput);
}

// ========================================
// PASSWORD STRENGTH
// ========================================
function setupPasswordStrength() {
    const passwordInput = document.getElementById('regPassword');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    if (!passwordInput || !strengthFill || !strengthText) return;
    
    passwordInput.addEventListener('input', () => {
        const password = passwordInput.value;
        const strength = calculatePasswordStrength(password);
        
        strengthFill.style.width = strength.percentage + '%';
        strengthFill.className = 'strength-fill strength-' + strength.level;
        strengthText.textContent = strength.text;
        strengthText.className = 'strength-text strength-' + strength.level;
    });
}

function calculatePasswordStrength(password) {
    if (password.length === 0) {
        return { level: 'weak', percentage: 0, text: 'Enter password' };
    }
    
    let strength = 0;
    if (password.length >= 1) strength += 10;
    if (password.length >= 4) strength += 20;
    if (password.length >= 7) strength += 25;
    if (password.length >= 10) strength += 25;
    if (/[a-z]/.test(password)) strength += 5;
    if (/[A-Z]/.test(password)) strength += 5;
    if (/[0-9]/.test(password)) strength += 5;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 5;
    
    if (strength <= 20) return { level: 'weak', percentage: 20, text: 'Weak' };
    if (strength <= 45) return { level: 'fair', percentage: 45, text: 'Fair' };
    if (strength <= 70) return { level: 'good', percentage: 70, text: 'Good' };
    return { level: 'strong', percentage: 100, text: 'Strong' };
}

// ========================================
// PASSWORD MATCH
// ========================================
function setupPasswordMatch() {
    const confirmPassword = document.getElementById('confirmPassword');
    const passwordMatch = document.getElementById('passwordMatch');
    const passwordMismatch = document.getElementById('passwordMismatch');
    
    if (!confirmPassword || !passwordMatch || !passwordMismatch) return;
    
    confirmPassword.addEventListener('input', () => {
        const password = document.getElementById('regPassword').value;
        const confirm = confirmPassword.value;
        
        if (confirm.length === 0) {
            passwordMatch.style.display = 'none';
            passwordMismatch.style.display = 'none';
        } else if (password === confirm) {
            passwordMatch.style.display = 'flex';
            passwordMismatch.style.display = 'none';
        } else {
            passwordMatch.style.display = 'none';
            passwordMismatch.style.display = 'flex';
        }
    });
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        if (button) button.textContent = '👁‍🗨';
    } else {
        input.type = 'password';
        if (button) button.textContent = '👁';
    }
}

// ========================================
// FORM LISTENERS
// ========================================
function setupFormListeners() {
    // Registration Form
    const regForm = document.getElementById('registrationForm');
    if (regForm) {
        regForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Registration form submitted');
            
            if (!validateRegistration()) {
                console.log('Validation failed');
                return;
            }
            
            const btn = e.target.querySelector('.btn-primary');
            showLoading(btn);
            
            try {
                const response = await fetch(API_BASE + '/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        first_name: document.getElementById('firstName').value,
                        last_name: document.getElementById('lastName').value,
                        email: document.getElementById('regEmail').value,
                        phone: document.getElementById('phoneNumber').value,
                        password: document.getElementById('regPassword').value,
                        confirm_password: document.getElementById('confirmPassword').value,
                        account_type: selectedAccountType,
                        company_name: document.getElementById('companyName')?.value || ''
                    })
                });
                
                const json = await response.json();
                hideLoading(btn);
                
                console.log('Registration response:', json);
                
                if (json.success) {
                    userData = {
                        firstName: document.getElementById('firstName').value,
                        lastName: document.getElementById('lastName').value,
                        email: document.getElementById('regEmail').value,
                        phone: document.getElementById('phoneNumber').value,
                        accountType: selectedAccountType,
                        company: document.getElementById('companyName')?.value || ''
                    };
                    switchView('setup2fa');
                } else {
                    alert(json.error || 'Registration failed');
                }
            } catch (error) {
                console.error('Registration error:', error);
                hideLoading(btn);
                alert('Network error. Please try again.');
            }
        });
    }
    
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Login form submitted');
            
            if (!validateLogin()) {
                console.log('Login validation failed');
                return;
            }
            
            const btn = e.target.querySelector('.btn-primary');
            showLoading(btn);
            
            try {
                const response = await fetch(API_BASE + '/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: document.getElementById('loginEmail').value,
                        password: document.getElementById('loginPassword').value
                    })
                });
                
                const json = await response.json();
                hideLoading(btn);
                
                console.log('Login response:', json);
                
                if (json.success) {
                    localStorage.setItem('bocra_website_token', json.data.token);
                    userData = json.data.user;
                    
                    if (json.data.requires_2fa) {
                        switchView('verify2fa');
                    } else {
                        switchView('dashboard');
                    }
                } else {
                    alert(json.error || 'Login failed');
                }
            } catch (error) {
                console.error('Login error:', error);
                hideLoading(btn);
                alert('Network error. Please try again.');
            }
        });
    }
}

// ========================================
// VALIDATION
// ========================================
function validateRegistration() {
    let isValid = true;
    
    const firstName = document.getElementById('firstName');
    if (!firstName.value.trim()) {
        showError(firstName);
        isValid = false;
    } else {
        hideError(firstName);
    }
    
    const lastName = document.getElementById('lastName');
    if (!lastName.value.trim()) {
        showError(lastName);
        isValid = false;
    } else {
        hideError(lastName);
    }
    
    const email = document.getElementById('regEmail');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        showError(email);
        isValid = false;
    } else {
        hideError(email);
    }
    
    if (!selectedAccountType) {
        const accountTypeInput = document.getElementById('accountType');
        if (accountTypeInput) showError(accountTypeInput);
        isValid = false;
    }
    
    if (selectedAccountType === 'company') {
        const companyName = document.getElementById('companyName');
        if (companyName && !companyName.value.trim()) {
            showError(companyName);
            isValid = false;
        } else if (companyName) {
            hideError(companyName);
        }
    }
    
    const password = document.getElementById('regPassword');
    if (password.value.length < 8) {
        showError(password);
        isValid = false;
    } else {
        hideError(password);
    }
    
    const confirmPassword = document.getElementById('confirmPassword');
    if (password.value !== confirmPassword.value) {
        showError(confirmPassword);
        isValid = false;
    } else {
        hideError(confirmPassword);
    }
    
    return isValid;
}

function validateLogin() {
    let isValid = true;
    
    const email = document.getElementById('loginEmail');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        showError(email);
        isValid = false;
    } else {
        hideError(email);
    }
    
    const password = document.getElementById('loginPassword');
    if (!password.value) {
        showError(password);
        isValid = false;
    } else {
        hideError(password);
    }
    
    return isValid;
}

// ========================================
// VIEW SWITCHING
// ========================================
function switchView(viewName) {
    console.log('Switching to view:', viewName);
    
    const allViews = document.querySelectorAll('.view-content, .form-content');
    allViews.forEach(v => v.classList.remove('active'));
    
    const targetView = document.getElementById(viewName + 'View');
    if (targetView) {
        targetView.classList.add('active');
    }
    
    if (viewName === 'dashboard') {
        showDashboard();
    } else {
        hideDashboard();
    }
    
    if (viewName === 'verify2fa' && userData.email) {
        const emailElement = document.getElementById('verifyUserEmail');
        if (emailElement) emailElement.textContent = userData.email;
    }
}

// ========================================
// DASHBOARD
// ========================================
function showDashboard() {
    const mainContent = document.querySelector('.main-content');
    const dashTopbar = document.querySelector('.dashboard-topbar');
    const dashContent = document.querySelector('.dashboard-content');
    
    if (mainContent) mainContent.style.display = 'none';
    if (dashTopbar) dashTopbar.classList.add('show');
    if (dashContent) dashContent.classList.add('show');
    
    fillDashboardData();
}

function hideDashboard() {
    const mainContent = document.querySelector('.main-content');
    const dashTopbar = document.querySelector('.dashboard-topbar');
    const dashContent = document.querySelector('.dashboard-content');
    
    if (mainContent) mainContent.style.display = 'flex';
    if (dashTopbar) dashTopbar.classList.remove('show');
    if (dashContent) dashContent.classList.remove('show');
}

function fillDashboardData() {
    if (!userData.firstName) return;
    
    const initials = (userData.firstName.charAt(0) + userData.lastName.charAt(0)).toUpperCase();
    const fullName = userData.firstName + ' ' + userData.lastName;
    
    const elements = {
        'dashAvatar': initials,
        'dashUserName': fullName,
        'dashWelcome': 'Welcome, ' + userData.firstName,
        'profileEmail': userData.email,
        'profilePhone': userData.phone || 'Not provided',
        'profileAccountType': userData.accountType || 'Individual'
    };
    
    Object.keys(elements).forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = elements[id];
    });
}

async function logout() {
    try {
        const token = localStorage.getItem('bocra_website_token');
        await fetch(API_BASE + '/logout.php', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token }
        });
    } catch (error) {
        console.error('Logout error:', error);
    }
    
    localStorage.removeItem('bocra_website_token');
    userData = {};
    selectedAccountType = '';
    switchView('login');
    switchTab('login');
    
    // Reset forms
    const regForm = document.getElementById('registrationForm');
    const loginForm = document.getElementById('loginForm');
    if (regForm) regForm.reset();
    if (loginForm) loginForm.reset();
}

// ========================================
// OTP BOXES
// ========================================
function setupOtpBoxes() {
    setupOtpBoxes = initOtpBoxes('setup-otp-');
    verifyOtpBoxes = initOtpBoxes('verify-otp-');
}

function initOtpBoxes(prefix) {
    const boxes = [];
    for (let i = 1; i <= 6; i++) {
        const box = document.getElementById(prefix + i);
        if (box) {
            boxes.push(box);
            
            box.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && i < 6) {
                    boxes[i].focus();
                }
            });
            
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && i > 1) {
                    boxes[i - 2].focus();
                }
            });
            
            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text');
                const digits = pastedData.replace(/\D/g, '').slice(0, 6);
                digits.split('').forEach((digit, index) => {
                    if (boxes[index]) boxes[index].value = digit;
                });
                if (digits.length > 0) boxes[Math.min(digits.length - 1, 5)].focus();
            });
        }
    }
    return boxes;
}

function getOtpValue(prefix) {
    const boxes = prefix === 'setup-otp-' ? setupOtpBoxes : verifyOtpBoxes;
    return boxes.map(box => box.value).join('');
}

function clearOtp(prefix) {
    const boxes = prefix === 'setup-otp-' ? setupOtpBoxes : verifyOtpBoxes;
    boxes.forEach(box => box.value = '');
    if (boxes[0]) boxes[0].focus();
}

function shakeOtpBoxes(prefix) {
    const boxes = prefix === 'setup-otp-' ? setupOtpBoxes : verifyOtpBoxes;
    boxes.forEach(box => box.classList.add('otp-shake'));
    setTimeout(() => boxes.forEach(box => box.classList.remove('otp-shake')), 500);
}

// ========================================
// 2FA VERIFICATION
// ========================================
async function verifySetup() {
    const termsCheckbox = document.getElementById('setupTerms');
    const otpValue = getOtpValue('setup-otp-');
    const errorElement = document.getElementById('setupError');
    
    if (!termsCheckbox || !termsCheckbox.checked) {
        alert('Please accept the terms to continue');
        return;
    }
    
    if (otpValue.length !== 6) {
        shakeOtpBoxes('setup-otp-');
        if (errorElement) {
            errorElement.style.display = 'block';
            errorElement.textContent = 'Please enter all 6 digits';
        }
        return;
    }
    
    if (errorElement) errorElement.style.display = 'none';
    const btn = event.target;
    showLoading(btn);
    
    try {
        const response = await fetch(API_BASE + '/verify-otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: otpValue })
        });
        
        const json = await response.json();
        hideLoading(btn);
        
        if (json.success) {
            switchView('login');
            switchTab('login');
            document.getElementById('loginEmail').value = userData.email;
            
            const successBanner = document.getElementById('successBanner');
            if (successBanner) {
                successBanner.classList.add('show');
                setTimeout(() => successBanner.classList.remove('show'), 5000);
            }
        } else {
            if (errorElement) {
                errorElement.style.display = 'block';
                errorElement.textContent = json.error || 'Verification failed';
            }
            shakeOtpBoxes('setup-otp-');
        }
    } catch (error) {
        console.error('2FA setup error:', error);
        hideLoading(btn);
        alert('Network error. Please try again.');
    }
}

async function verifyLogin() {
    const otpValue = getOtpValue('verify-otp-');
    const errorElement = document.getElementById('verifyError');
    
    if (otpValue.length !== 6) {
        shakeOtpBoxes('verify-otp-');
        if (errorElement) {
            errorElement.style.display = 'block';
            errorElement.textContent = 'Please enter all 6 digits';
        }
        return;
    }
    
    const btn = event.target;
    showLoading(btn);
    
    try {
        const token = localStorage.getItem('bocra_website_token');
        const response = await fetch(API_BASE + '/verify-otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ code: otpValue })
        });
        
        const json = await response.json();
        hideLoading(btn);
        
        if (json.success) {
            userData = json.data.user;
            switchView('dashboard');
        } else {
            if (errorElement) {
                errorElement.style.display = 'block';
                errorElement.textContent = json.error || 'Verification failed';
            }
            shakeOtpBoxes('verify-otp-');
        }
    } catch (error) {
        console.error('2FA verify error:', error);
        hideLoading(btn);
        alert('Network error. Please try again.');
    }
}

// ========================================
// UTILITY FUNCTIONS
// ========================================
function showError(input) {
    if (!input) return;
    input.classList.add('error');
    const errorMsg = input.parentElement.querySelector('.error-message');
    if (errorMsg) errorMsg.classList.add('show');
}

function hideError(input) {
    if (!input) return;
    input.classList.remove('error');
    const errorMsg = input.parentElement.querySelector('.error-message');
    if (errorMsg) errorMsg.classList.remove('show');
}

function showLoading(btn) {
    if (!btn) return;
    btn.classList.add('loading');
    btn.disabled = true;
}

function hideLoading(btn) {
    if (!btn) return;
    btn.classList.remove('loading');
    btn.disabled = false;
}
