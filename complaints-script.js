// BOCRA Complaints Page JavaScript

function showSection(id) {
  document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

  const target = document.getElementById(id);
  if (target) {
    target.classList.add('active');
    setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
  }

  const btn = document.querySelector(`.tab-btn[data-section="${id}"]`);
  if (btn) btn.classList.add('active');
}

let currentStep = 1;

function nextStep(fromStep) {
  if (!validateStep(fromStep)) return;

  const current = document.getElementById(`step${fromStep}`);
  const next = document.getElementById(`step${fromStep + 1}`);

  if (current) current.classList.remove('active');
  if (next) next.classList.add('active');

  currentStep = fromStep + 1;
  updateProgressBar(currentStep);
  scrollToForm();
}

function prevStep(fromStep) {
  const current = document.getElementById(`step${fromStep}`);
  const prev = document.getElementById(`step${fromStep - 1}`);

  if (current) current.classList.remove('active');
  if (prev) prev.classList.add('active');

  currentStep = fromStep - 1;
  updateProgressBar(currentStep);
  scrollToForm();
}

function updateProgressBar(step) {
  document.querySelectorAll('.progress-step').forEach((el, i) => {
    const stepNum = i + 1;
    el.classList.remove('active');
    if (stepNum === step) el.classList.add('active');

    const icon = el.querySelector('.ps-icon i');
    const icons = ['fa-user', 'fa-building', 'fa-file-alt'];
    if (icon) {
      icon.className = stepNum < step ? 'fas fa-check' : `fas ${icons[i]}`;
    }
  });
}

function scrollToForm() {
  const container = document.querySelector('.form-container');
  if (container) {
    setTimeout(() => container.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
  }
}

function validateStep(step) {
  let valid = true;

  if (step === 1) {
    valid = validateField('fullName', 'Full name is required.')
          & validateField('idNumber', 'ID or passport number is required.')
          & validateEmail('email', 'Please enter a valid email address.')
          & validatePhone('telephone', 'Please enter a valid phone number.')
          & validateField('address', 'Please enter your address.')
          & validateSelect('complaintType', 'Please select your complainant type.');
  }

  if (step === 2) {
    valid = validateSelect('provider', 'Please select a service provider.')
          & validateSelect('serviceType', 'Please select a sector.')
          & validateSelect('category', 'Please select a complaint category.')
          & validateField('complaintDate', 'Please select the date.')
          & validateRadio('contacted', 'contacted-err', 'Please select an option.');
  }

  if (step === 3) {
    valid = validateMinLength('complaintDesc', 20, 'Please describe your complaint (at least 20 characters).')
          & validateField('relief', 'Please describe the outcome you are seeking.')
          & validateCheckbox('consent', 'consent-err', 'You must confirm the information is accurate.')
          & validateCheckbox('privacy', 'privacy-err', 'You must consent to data processing.');
  }

  return valid;
}

function validateField(id, msg) {
  const el = document.getElementById(id);
  const err = document.getElementById(`${id}-err`);
  if (!el) return true;
  if (!el.value.trim()) {
    showError(el, err, msg); return false;
  }
  clearError(el, err); return true;
}

function validateEmail(id, msg) {
  const el = document.getElementById(id);
  const err = document.getElementById(`${id}-err`);
  if (!el) return true;
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!re.test(el.value.trim())) {
    showError(el, err, msg); return false;
  }
  clearError(el, err); return true;
}

function validatePhone(id, msg) {
  const el = document.getElementById(id);
  const err = document.getElementById(`${id}-err`);
  if (!el) return true;
  const re = /^(\+267|267|0)?[0-9\s\-]{7,13}$/;
  if (!re.test(el.value.trim())) {
    showError(el, err, msg); return false;
  }
  clearError(el, err); return true;
}

function validateSelect(id, msg) {
  const el = document.getElementById(id);
  const err = document.getElementById(`${id}-err`);
  if (!el) return true;
  if (!el.value) {
    showError(el, err, msg); return false;
  }
  clearError(el, err); return true;
}

function validateMinLength(id, min, msg) {
  const el = document.getElementById(id);
  const err = document.getElementById(`${id}-err`);
  if (!el) return true;
  if (el.value.trim().length < min) {
    showError(el, err, msg); return false;
  }
  clearError(el, err); return true;
}

function validateRadio(name, errId, msg) {
  const checked = document.querySelector(`input[name="${name}"]:checked`);
  const err = document.getElementById(errId);
  if (!checked) {
    if (err) err.textContent = msg;
    return false;
  }
  if (err) err.textContent = '';
  return true;
}

function validateCheckbox(id, errId, msg) {
  const el = document.getElementById(id);
  const err = document.getElementById(errId);
  if (!el) return true;
  if (!el.checked) {
    if (err) err.textContent = msg;
    return false;
  }
  if (err) err.textContent = '';
  return true;
}

function showError(el, errEl, msg) {
  el.classList.add('invalid');
  if (errEl) errEl.textContent = msg;
}

function clearError(el, errEl) {
  el.classList.remove('invalid');
  if (errEl) errEl.textContent = '';
}

document.addEventListener('input', e => {
  const el = e.target;
  const err = document.getElementById(`${el.id}-err`);
  if (err) clearError(el, err);
});

const complaintDesc = document.getElementById('complaintDesc');
const charCount = document.getElementById('charCount');

if (complaintDesc && charCount) {
  complaintDesc.addEventListener('input', () => {
    const len = complaintDesc.value.length;
    charCount.textContent = `${len} / 1000 characters`;
    if (len > 1000) complaintDesc.value = complaintDesc.value.substring(0, 1000);
  });
}

const complaintForm = document.getElementById('complaintForm');
const successPanel = document.getElementById('successPanel');

if (complaintForm) {
  complaintForm.addEventListener('submit', async e => {
    e.preventDefault();
    console.log('Form submitted - starting validation');
    
    if (!validateStep(3)) {
      console.log('Validation failed at step 3');
      alert('Please fill in all required fields correctly.');
      return;
    }

    console.log('Validation passed - preparing data');
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    // Prepare complaint data
    const formData = {
      complainant_name: document.getElementById('fullName').value.trim(),
      complainant_email: document.getElementById('email').value.trim(),
      complainant_phone: document.getElementById('telephone').value.trim(),
      complaint_type: document.getElementById('category').value,
      sector: document.getElementById('serviceType').value,
      service_provider: document.getElementById('provider').value,
      subject: `${document.getElementById('category').value} - ${document.getElementById('provider').value}`,
      description: document.getElementById('complaintDesc').value.trim(),
      desired_outcome: document.getElementById('relief').value.trim()
    };

    console.log('Submitting complaint data:', formData);

    try {
      console.log('Calling API: api/submit-complaint.php');
      const response = await fetch('api/submit-complaint.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      console.log('API Response status:', response.status);
      const result = await response.json();
      console.log('API Result:', result);

      if (result.success) {
        console.log('Success! Complaint number:', result.complaint_number);
        complaintForm.style.display = 'none';
        successPanel.classList.add('show');
        document.getElementById('refNumber').textContent = result.complaint_number;
        successPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        console.error('API returned error:', result.message);
        alert('Error: ' + (result.message || 'Failed to submit complaint'));
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Complaint';
      }
    } catch (error) {
      console.error('Network/Submit error:', error);
      alert('Network error. Please check console for details.');
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Complaint';
    }
  });
} else {
  console.error('Complaint form not found!');
}

function resetForm() {
  if (complaintForm && successPanel) {
    complaintForm.reset();
    complaintForm.style.display = '';
    successPanel.classList.remove('show');

    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.getElementById('step1').classList.add('active');
    currentStep = 1;
    updateProgressBar(1);

    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Complaint';
    }

    complaintForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const dateInput = document.getElementById('complaintDate');
  if (dateInput) {
    dateInput.max = new Date().toISOString().split('T')[0];
  }

  showSection('consumer');
  updateProgressBar(1);
});
