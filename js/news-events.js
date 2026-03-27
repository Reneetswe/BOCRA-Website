// BOCRA News & Events - Main JavaScript
// File: js/news-events.js

let currentCategory = 'All';
let currentYear = 'All';

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    renderNewsCards(newsData);
    setupFilterListeners();
});

// Render news cards
function renderNewsCards(data) {
    const newsGrid = document.getElementById('newsGrid');
    const noResults = document.getElementById('noResults');
    const resultsCount = document.getElementById('resultsCount');
    
    newsGrid.innerHTML = '';
    
    if (data.length === 0) {
        noResults.style.display = 'block';
        resultsCount.textContent = '0';
        return;
    }
    
    noResults.style.display = 'none';
    resultsCount.textContent = data.length;
    
    data.forEach(item => {
        const card = createNewsCard(item);
        newsGrid.appendChild(card);
    });
}

// Create individual news card
function createNewsCard(item) {
    const card = document.createElement('div');
    card.className = 'news-card';
    card.onclick = () => openDetail(item.id);
    
    const formattedDate = formatDate(item.date);
    
    card.innerHTML = `
        <img src="${item.image}" alt="${item.title}" class="news-card-image" onerror="this.src='assets/images/bocra-logo.png'">
        <div class="news-card-content">
            <div class="news-card-meta">
                <span class="news-category">${item.category}</span>
                <span class="news-date">${formattedDate}</span>
            </div>
            <h3>${item.title}</h3>
            <p>${item.summary}</p>
        </div>
        <div class="news-card-footer">
            <a href="#" class="read-more" onclick="event.preventDefault(); openDetail(${item.id})">
                Read More →
            </a>
        </div>
    `;
    
    return card;
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-GB', options);
}

// Setup filter listeners
function setupFilterListeners() {
    // Category filters
    const categoryButtons = document.querySelectorAll('[data-category]');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all category buttons
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            currentCategory = this.dataset.category;
            applyFilters();
        });
    });
    
    // Year filters
    const yearButtons = document.querySelectorAll('[data-year]');
    yearButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all year buttons
            yearButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            currentYear = this.dataset.year;
            applyFilters();
        });
    });
}

// Apply filters
function applyFilters() {
    let filteredData = newsData;
    
    // Filter by category
    if (currentCategory !== 'All') {
        filteredData = filteredData.filter(item => item.category === currentCategory);
    }
    
    // Filter by year
    if (currentYear !== 'All') {
        filteredData = filteredData.filter(item => item.year === parseInt(currentYear));
    }
    
    // Sort by date (newest first)
    filteredData.sort((a, b) => new Date(b.date) - new Date(a.date));
    
    renderNewsCards(filteredData);
}

// Open detail view
function openDetail(itemId) {
    const item = newsData.find(i => i.id === itemId);
    
    if (!item) return;
    
    document.getElementById('detailImage').src = item.image;
    document.getElementById('detailCategory').textContent = item.category;
    document.getElementById('detailDate').textContent = formatDate(item.date);
    document.getElementById('detailTitle').textContent = item.title;
    document.getElementById('detailContent').textContent = item.content.trim();
    
    const modal = document.getElementById('detailModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close detail view
function closeDetail() {
    const modal = document.getElementById('detailModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetail();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetail();
    }
});
