/* ══════════════════════════════════════════════════════
   BOCRA REGULATIONS PAGE — JAVASCRIPT
══════════════════════════════════════════════════════ */

// Track which tabs are active per sector
const activeTabs = {
  telecom:      'overview',
  broadcasting: 'overview',
  postal:       'overview',
  internet:     'overview'
};

// ── TOGGLE SECTOR ACCORDION ──────────────────────────
function toggleSector(id) {
  const body  = document.getElementById(`${id}-body`);
  const arrow = document.getElementById(`${id}-arrow`);

  const isOpen = body.classList.contains('open');

  // Close this sector
  body.classList.toggle('open', !isOpen);
  if (arrow) arrow.classList.toggle('open', !isOpen);

  // Update hint text
  const hint = body.closest('.sector-section').querySelector('.sector-toggle-hint');
  if (hint) hint.textContent = isOpen ? 'Explore sector' : 'Collapse';
}

// ── SHOW TAB ─────────────────────────────────────────
function showTab(sector, tab) {
  // Hide all stab-contents in this sector
  const sectionEl = document.getElementById(sector);
  if (!sectionEl) return;

  sectionEl.querySelectorAll('.stab-content').forEach(el => el.classList.remove('active'));
  sectionEl.querySelectorAll('.stab').forEach(el => el.classList.remove('active'));

  // Show target
  const content = document.getElementById(`${sector}-${tab}`);
  if (content) content.classList.add('active');

  // Activate button
  const tabs = document.getElementById(`${sector}-tabs`);
  if (tabs) {
    const btns = tabs.querySelectorAll('.stab');
    btns.forEach(btn => {
      if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(`'${tab}'`)) {
        btn.classList.add('active');
      }
    });
  }

  activeTabs[sector] = tab;
  
  // If switching to ISP providers tab, load the data
  if (sector === 'internet' && tab === 'providers') {
    loadISPProviders();
  }
}

// ── JUMP BUTTONS ─────────────────────────────────────
document.querySelectorAll('.jump-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const sectorId = btn.getAttribute('data-sector');
    const sectionEl = document.getElementById(sectorId);
    if (!sectionEl) return;

    // Open body if not already open
    const body  = document.getElementById(`${sectorId}-body`);
    const arrow = document.getElementById(`${sectorId}-arrow`);
    if (body && !body.classList.contains('open')) {
      body.classList.add('open');
      if (arrow) arrow.classList.add('open');
      const hint = sectionEl.querySelector('.sector-toggle-hint');
      if (hint) hint.textContent = 'Collapse';
    }

    // Smooth scroll
    setTimeout(() => {
      sectionEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 60);
  });
});

// ── OPEN FIRST SECTOR ON LOAD ─────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Telecom open by default
  const telecomBody  = document.getElementById('telecom-body');
  const telecomArrow = document.getElementById('telecom-arrow');
  if (telecomBody)  telecomBody.classList.add('open');
  if (telecomArrow) telecomArrow.classList.add('open');
  const hint = document.querySelector('#telecom .sector-toggle-hint');
  if (hint) hint.textContent = 'Collapse';
});

// ── LOAD ISP PROVIDERS FROM API ──────────────────────
let ispDataLoaded = false;

async function loadISPProviders() {
  // Only load once
  if (ispDataLoaded) return;
  
  const container = document.getElementById('isp-container');
  const loading = document.getElementById('isp-loading');
  
  if (!container || !loading) return;
  
  try {
    // Fetch ISP data from API
    const response = await fetch('api/get-isp-providers.php');
    const result = await response.json();
    
    if (!result.success || !result.providers) {
      throw new Error('Invalid API response');
    }
    
    const data = result.providers;
    
    // Hide loading, show container
    loading.style.display = 'none';
    container.style.display = 'block';
    
    // Create ISP grid
    const grid = document.createElement('div');
    grid.className = 'isp-grid';
    
    // Generate ISP cards
    data.forEach((isp, index) => {
      const card = document.createElement('div');
      card.className = 'isp-card';
      
      const name = isp.name || 'Unknown ISP';
      const contact = isp.contact || '';
      const phone = isp.phone || '';
      const email = isp.email || '';
      const address = isp.address || '';
      const website = isp.website || '';
      
      card.innerHTML = `
        <div class="isp-header">
          <div class="isp-icon">
            <i class="fas fa-network-wired"></i>
          </div>
          <h3 class="isp-name">${escapeHtml(name)}</h3>
        </div>
        
        ${contact ? `
        <div class="isp-detail">
          <i class="fas fa-user"></i>
          <div>
            <div class="isp-detail-label">Contact:</div>
            <div class="isp-detail-value">${escapeHtml(contact)}</div>
          </div>
        </div>
        ` : ''}
        
        ${phone ? `
        <div class="isp-detail">
          <i class="fas fa-phone"></i>
          <div>
            <div class="isp-detail-label">Phone:</div>
            <div class="isp-detail-value">${escapeHtml(phone)}</div>
          </div>
        </div>
        ` : ''}
        
        ${email ? `
        <div class="isp-detail">
          <i class="fas fa-envelope"></i>
          <div>
            <div class="isp-detail-label">Email:</div>
            <div class="isp-detail-value"><a href="mailto:${escapeHtml(email)}">${escapeHtml(email)}</a></div>
          </div>
        </div>
        ` : ''}
        
        ${address ? `
        <div class="isp-detail">
          <i class="fas fa-map-marker-alt"></i>
          <div>
            <div class="isp-detail-label">Address:</div>
            <div class="isp-detail-value">${escapeHtml(address)}</div>
          </div>
        </div>
        ` : ''}
        
        ${website ? `
        <div class="isp-detail">
          <i class="fas fa-globe"></i>
          <div>
            <div class="isp-detail-label">Website:</div>
            <div class="isp-detail-value"><a href="${escapeHtml(website)}" target="_blank">${escapeHtml(website)}</a></div>
          </div>
        </div>
        ` : ''}
      `;
      
      grid.appendChild(card);
    });
    
    container.appendChild(grid);
    
    // Add summary
    const summary = document.createElement('div');
    summary.className = 'info-note blue-note';
    summary.style.marginTop = '2rem';
    summary.innerHTML = `
      <i class="fas fa-info-circle"></i>
      <p><strong>${data.length} Licensed Internet Service Providers</strong> are currently operating in Botswana under BOCRA regulation. All ISPs must comply with quality of service standards and licensing requirements.</p>
    `;
    container.appendChild(summary);
    
    ispDataLoaded = true;
    
  } catch (error) {
    console.error('Error loading ISP data:', error);
    loading.innerHTML = `
      <div style="color: var(--rose); text-align: center;">
        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
        <p><strong>Unable to load Internet Service Providers data</strong></p>
        <p style="font-size: 0.875rem; color: var(--mid);">Please contact the administrator if this issue persists.</p>
      </div>
    `;
  }
}

// Helper function to escape HTML
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
