import { DOCUMENTS, CATEGORIES, MAIN_CATEGORIES } from './documents-data.js';

// App State
let state = {
    searchQuery: '',
    selectedCategory: null,
    selectedMainCategory: null,
    selectedDoc: null,
    viewMode: 'list',
    sortBy: 'newest'
};

// Initialize App
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    attachEventListeners();
});

// Initialize Application
function initializeApp() {
    renderFeaturedDocuments();
    renderCategoryPills();
    renderDocuments();
    renderSuggestions();
}

// Render Featured Documents
function renderFeaturedDocuments() {
    const featuredGrid = document.getElementById('featuredGrid');
    const featuredDocs = DOCUMENTS.filter(doc => doc.relatedDocumentIds && doc.relatedDocumentIds.length > 0).slice(0, 3);
    
    if (featuredDocs.length === 0) {
        document.getElementById('featuredSection').style.display = 'none';
        return;
    }
    
    featuredGrid.innerHTML = featuredDocs.map(doc => `
        <div class="featured-card" data-id="${doc.id}">
            <div class="featured-card-header">
                <div class="featured-icon">
                    <i class="fas ${getFileIcon(doc.fileType)}"></i>
                </div>
                <h3 class="featured-card-title">${doc.title}</h3>
            </div>
            
            <div class="featured-related">
                <div class="featured-related-title">Linked Documents</div>
                ${doc.relatedDocumentIds.slice(0, 2).map(relId => {
                    const relDoc = DOCUMENTS.find(d => d.id === relId);
                    return relDoc ? `
                        <div class="related-doc">
                            <i class="fas fa-arrow-up-right"></i>
                            <span>${relDoc.title}</span>
                        </div>
                    ` : '';
                }).join('')}
                ${doc.relatedDocumentIds.length > 2 ? `
                    <div style="font-size: 10px; color: #888; margin-top: 8px; font-weight: 900;">
                        +${doc.relatedDocumentIds.length - 2} more related
                    </div>
                ` : ''}
            </div>
            
            <div class="featured-footer">
                <span class="featured-category">${doc.category}</span>
                <span class="featured-link">
                    View Details
                    <i class="fas fa-chevron-right"></i>
                </span>
            </div>
        </div>
    `).join('');
    
    // Attach click handlers
    featuredGrid.querySelectorAll('.featured-card').forEach(card => {
        card.addEventListener('click', () => {
            const docId = card.dataset.id;
            openDocumentModal(docId);
        });
    });
}

// Render Category Pills
function renderCategoryPills() {
    const categoryPills = document.getElementById('categoryPills');
    const categoryCounts = {};
    
    DOCUMENTS.forEach(doc => {
        categoryCounts[doc.category] = (categoryCounts[doc.category] || 0) + 1;
    });
    
    const allPill = `
        <button class="category-pill ${!state.selectedCategory ? 'active' : ''}" data-category="all">
            All Resources
            <span class="pill-count">${DOCUMENTS.length}</span>
        </button>
    `;
    
    const categoryPillsHTML = CATEGORIES.map(category => `
        <button class="category-pill ${state.selectedCategory === category ? 'active' : ''}" data-category="${category}">
            ${category}
            <span class="pill-count">${categoryCounts[category] || 0}</span>
        </button>
    `).join('');
    
    categoryPills.innerHTML = allPill + categoryPillsHTML;
}

// Render Documents
function renderDocuments() {
    const documentsGrid = document.getElementById('documentsGrid');
    const filteredDocs = getFilteredDocuments();
    
    // Update results count
    document.getElementById('resultsCount').textContent = filteredDocs.length;
    document.getElementById('resultsCategory').textContent = state.selectedCategory || state.selectedMainCategory || 'All Resources';
    
    // Update breadcrumb
    const breadcrumbCategory = document.getElementById('breadcrumbCategory');
    if (state.selectedMainCategory || state.selectedCategory) {
        breadcrumbCategory.innerHTML = `<i class="fas fa-chevron-right"></i><span>${state.selectedCategory || state.selectedMainCategory}</span>`;
    } else {
        breadcrumbCategory.innerHTML = '';
    }
    
    // Show/hide clear filters button
    const clearBtn = document.getElementById('clearFiltersBtn');
    if (state.selectedCategory || state.selectedMainCategory) {
        clearBtn.style.display = 'flex';
    } else {
        clearBtn.style.display = 'none';
    }
    
    // Update view mode class
    documentsGrid.className = `documents-grid ${state.viewMode}-view`;
    
    if (filteredDocs.length === 0) {
        documentsGrid.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="empty-title">No Resources Found</h3>
                <p class="empty-description">
                    We couldn't find any documents matching your search criteria. Try adjusting your filters or search terms.
                </p>
                <button class="btn btn-primary" id="resetFiltersBtn">
                    <i class="fas fa-redo"></i>
                    Reset Filters
                </button>
            </div>
        `;
        
        document.getElementById('resetFiltersBtn')?.addEventListener('click', resetFilters);
        return;
    }
    
    documentsGrid.innerHTML = filteredDocs.map(doc => {
        const isNew = new Date(doc.date).getFullYear() >= 2023;
        
        return `
            <div class="document-card">
                <div class="document-icon-wrapper">
                    <div class="document-icon">
                        <i class="fas ${getFileIcon(doc.fileType)}"></i>
                    </div>
                    ${isNew ? '<div class="new-badge">NEW</div>' : ''}
                </div>
                
                <div class="document-content">
                    <h3 class="document-title" data-id="${doc.id}">${highlightText(doc.title, state.searchQuery)}</h3>
                    <div class="document-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar"></i>
                            ${formatDate(doc.date)}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-file"></i>
                            ${doc.fileSize} • ${doc.fileType}
                        </span>
                        <span class="document-category">${doc.category}</span>
                    </div>
                </div>
                
                <div class="document-actions">
                    <a href="${doc.url}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download"></i>
                        Download
                    </a>
                    <a href="${doc.url}" target="_blank" class="btn btn-secondary btn-icon" title="View Online">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <button class="btn btn-secondary btn-icon copy-link-btn" data-url="${doc.url}" title="Copy Link">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    // Attach event listeners
    documentsGrid.querySelectorAll('.document-title').forEach(title => {
        title.addEventListener('click', () => {
            openDocumentModal(title.dataset.id);
        });
    });
    
    documentsGrid.querySelectorAll('.copy-link-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            copyToClipboard(btn.dataset.url, btn);
        });
    });
}

// Get Filtered Documents
function getFilteredDocuments() {
    let results = [...DOCUMENTS];
    
    if (state.searchQuery.trim()) {
        const query = state.searchQuery.toLowerCase();
        results = results.filter(doc => 
            doc.title.toLowerCase().includes(query) ||
            doc.category.toLowerCase().includes(query) ||
            doc.mainCategory.toLowerCase().includes(query)
        );
    }
    
    if (state.selectedMainCategory) {
        results = results.filter(doc => doc.mainCategory === state.selectedMainCategory);
    }
    
    if (state.selectedCategory) {
        results = results.filter(doc => doc.category === state.selectedCategory);
    }
    
    // Sort documents
    results.sort((a, b) => {
        if (state.sortBy === 'newest') {
            return new Date(b.date) - new Date(a.date);
        } else if (state.sortBy === 'oldest') {
            return new Date(a.date) - new Date(b.date);
        } else {
            return a.title.localeCompare(b.title);
        }
    });
    
    return results;
}

// Open Document Modal
function openDocumentModal(docId) {
    const doc = DOCUMENTS.find(d => d.id === docId);
    if (!doc) return;
    
    state.selectedDoc = doc;
    const modal = document.getElementById('documentModal');
    const modalContent = document.getElementById('documentModalContent');
    
    modalContent.innerHTML = `
        <button class="modal-close" id="closeDocModalBtn">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="document-modal-content">
            <div class="document-modal-header">
                <div class="document-modal-icon">
                    <i class="fas ${getFileIcon(doc.fileType)}"></i>
                </div>
                <div class="document-modal-info">
                    <div class="document-modal-badges">
                        <span class="modal-badge">${doc.category}</span>
                        <span class="modal-badge">${doc.mainCategory}</span>
                    </div>
                    <h2 class="document-modal-title">${doc.title}</h2>
                    <div class="document-modal-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar"></i>
                            ${formatDate(doc.date)}
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-file"></i>
                            ${doc.fileSize} • ${doc.fileType}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="document-modal-actions">
                <a href="${doc.url}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download"></i>
                    Download Resource
                </a>
                <button class="btn btn-secondary copy-link-btn" data-url="${doc.url}">
                    <i class="fas fa-copy"></i>
                    Copy Share Link
                </button>
            </div>
            
            ${doc.relatedDocumentIds && doc.relatedDocumentIds.length > 0 ? `
                <div class="related-documents">
                    <h3 class="related-documents-title">
                        <i class="fas fa-link"></i>
                        Related Documents
                    </h3>
                    ${doc.relatedDocumentIds.map(relId => {
                        const relDoc = DOCUMENTS.find(d => d.id === relId);
                        if (!relDoc) return '';
                        
                        return `
                            <div class="related-document-item" data-id="${relId}">
                                <div class="related-doc-icon">
                                    <i class="fas ${getFileIcon(relDoc.fileType)}"></i>
                                </div>
                                <div class="related-doc-content">
                                    <div class="related-doc-title">${relDoc.title}</div>
                                    <div class="related-doc-category">${relDoc.category}</div>
                                </div>
                                <i class="fas fa-arrow-right related-doc-arrow"></i>
                            </div>
                        `;
                    }).join('')}
                </div>
            ` : ''}
        </div>
    `;
    
    modal.classList.add('active');
    
    // Attach event listeners
    document.getElementById('closeDocModalBtn').addEventListener('click', closeDocumentModal);
    
    modalContent.querySelectorAll('.copy-link-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            copyToClipboard(btn.dataset.url, btn);
        });
    });
    
    modalContent.querySelectorAll('.related-document-item').forEach(item => {
        item.addEventListener('click', () => {
            openDocumentModal(item.dataset.id);
        });
    });
}

// Close Document Modal
function closeDocumentModal() {
    document.getElementById('documentModal').classList.remove('active');
    state.selectedDoc = null;
}

// Render Search Suggestions
function renderSuggestions() {
    const suggestionsGrid = document.getElementById('suggestionsGrid');
    suggestionsGrid.innerHTML = CATEGORIES.slice(0, 6).map(cat => `
        <button class="suggestion-pill" data-category="${cat}">${cat}</button>
    `).join('');
}

// Perform Search
function performSearch(query) {
    state.searchQuery = query;
    
    if (!query.trim()) {
        renderSuggestions();
        return;
    }
    
    const results = getFilteredDocuments().slice(0, 8);
    const searchResults = document.getElementById('searchResults');
    
    if (results.length === 0) {
        searchResults.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <p style="color: #888; font-weight: 700;">No results found for "${query}"</p>
            </div>
        `;
        return;
    }
    
    searchResults.innerHTML = `
        <div style="display: flex; flex-direction: column; gap: 10px;">
            ${results.map(doc => `
                <div class="search-result-item" data-id="${doc.id}">
                    <div class="search-result-icon">
                        <i class="fas ${getFileIcon(doc.fileType)}"></i>
                    </div>
                    <div class="search-result-content">
                        <div class="search-result-title">${doc.title}</div>
                        <div class="search-result-meta">${doc.category} • ${doc.mainCategory}</div>
                    </div>
                    <i class="fas fa-arrow-right search-result-arrow"></i>
                </div>
            `).join('')}
        </div>
    `;
    
    // Attach click handlers
    searchResults.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('click', () => {
            closeSearchModal();
            openDocumentModal(item.dataset.id);
        });
    });
}

// Attach Event Listeners
function attachEventListeners() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.getElementById('navMenu');
    
    mobileMenuToggle?.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        const icon = mobileMenuToggle.querySelector('i');
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
    });
    
    // Search modal
    document.getElementById('openSearchBtn').addEventListener('click', openSearchModal);
    document.getElementById('closeSearchBtn').addEventListener('click', closeSearchModal);
    document.getElementById('searchModalOverlay').addEventListener('click', closeSearchModal);
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        performSearch(e.target.value);
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openSearchModal();
        }
        if (e.key === 'Escape') {
            closeSearchModal();
            closeDocumentModal();
        }
    });
    
    // Category pills
    document.getElementById('categoryPills').addEventListener('click', (e) => {
        const pill = e.target.closest('.category-pill');
        if (!pill) return;
        
        const category = pill.dataset.category;
        state.selectedCategory = category === 'all' ? null : category;
        renderCategoryPills();
        renderDocuments();
    });
    
    // View mode toggle
    document.getElementById('listViewBtn').addEventListener('click', () => {
        state.viewMode = 'list';
        updateViewMode();
    });
    
    document.getElementById('gridViewBtn').addEventListener('click', () => {
        state.viewMode = 'grid';
        updateViewMode();
    });
    
    // Sort select
    document.getElementById('sortSelect').addEventListener('change', (e) => {
        state.sortBy = e.target.value;
        renderDocuments();
    });
    
    // Clear filters
    document.getElementById('clearFiltersBtn').addEventListener('click', resetFilters);
    
    // Document modal overlay
    document.getElementById('documentModalOverlay').addEventListener('click', closeDocumentModal);
    
    // Suggestion pills
    document.getElementById('suggestionsGrid').addEventListener('click', (e) => {
        const pill = e.target.closest('.suggestion-pill');
        if (!pill) return;
        
        state.selectedCategory = pill.dataset.category;
        closeSearchModal();
        renderCategoryPills();
        renderDocuments();
    });
}

// Helper Functions
function openSearchModal() {
    const modal = document.getElementById('searchModal');
    modal.classList.add('active');
    document.getElementById('searchInput').focus();
}

function closeSearchModal() {
    const modal = document.getElementById('searchModal');
    modal.classList.remove('active');
    document.getElementById('searchInput').value = '';
    state.searchQuery = '';
    renderSuggestions();
}

function updateViewMode() {
    document.getElementById('listViewBtn').classList.toggle('active', state.viewMode === 'list');
    document.getElementById('gridViewBtn').classList.toggle('active', state.viewMode === 'grid');
    renderDocuments();
}

function resetFilters() {
    state.selectedCategory = null;
    state.selectedMainCategory = null;
    state.searchQuery = '';
    renderCategoryPills();
    renderDocuments();
}

function getFileIcon(fileType) {
    const type = fileType.toUpperCase();
    if (type === 'PDF') return 'fa-file-pdf';
    if (['XLS', 'XLSX', 'CSV'].includes(type)) return 'fa-file-excel';
    if (['ZIP', 'RAR'].includes(type)) return 'fa-file-archive';
    if (['DOC', 'DOCX'].includes(type)) return 'fa-file-word';
    return 'fa-file-alt';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function highlightText(text, query) {
    if (!query.trim()) return text;
    const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<mark style="background: rgba(201, 162, 39, 0.3); color: #006B5E; font-weight: 900; border-radius: 3px; padding: 0 3px;">$1</mark>');
}

function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check" style="color: #4CAF50;"></i>';
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
