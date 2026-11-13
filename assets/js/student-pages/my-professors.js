document.addEventListener('DOMContentLoaded', function() {
    initCardInteractions();
    initEmailTracking();
    initResponsiveCards();
    initAccessibility();
    initLazyLoading();
    initModals();
    
    console.log('My Professors page loaded successfully');
});

function initModals() {
    const cards = document.querySelectorAll('.professor-card');
    
    cards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.professor-email')) {
                return;
            }
            
            const professorId = this.dataset.professorId;
            const classCode = this.dataset.classCode;
            const subject = this.dataset.subject;
            const section = this.dataset.section;
            const className = this.dataset.className;
            const schedule = this.dataset.schedule;
            const room = this.dataset.room;
            
            if (professorId) {
                openProfessorModal(professorId, {
                    classCode,
                    subject,
                    section,
                    className,
                    schedule,
                    room
                });
            }
        });
    });
    
    const modals = document.querySelectorAll('.professor-modal');
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.close-modal');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.professor-modal.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });
}

function openProfessorModal(professorId, classInfo) {
    const modal = document.getElementById('modal-' + professorId);
    const modalInfoContainer = document.getElementById('modal-info-' + professorId);
    
    if (modal && modalInfoContainer) {
        // Create modern modal layout with detail rows
        modalInfoContainer.innerHTML = `
            <div class="modal-detail-row">
                <span class="modal-detail-label">Subject Code:</span>
                <span class="modal-detail-value">${classInfo.subject}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Subject Name:</span>
                <span class="modal-detail-value">${classInfo.className}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Class:</span>
                <span class="modal-detail-value">${classInfo.subject} - ${classInfo.section}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Schedule:</span>
                <span class="modal-detail-value">${classInfo.schedule}</span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Room:</span>
                <span class="modal-detail-value">${classInfo.room}</span>
            </div>
        `;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function initCardInteractions() {
    const cards = document.querySelectorAll('.professor-card');
    
    cards.forEach(card => {
        card.setAttribute('tabindex', '0');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });

        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
}

function initEmailTracking() {
    const emailLinks = document.querySelectorAll('.professor-email');
    
    emailLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.stopPropagation();
            const professorName = this.closest('.professor-info')?.querySelector('.professor-name')?.textContent;
            const email = this.textContent.trim();
            
            console.log(`Email clicked: ${professorName} (${email})`);
        });
    });
}

function initResponsiveCards() {
    const grid = document.querySelector('.professors-grid');
    if (!grid) return;
    
    function adjustCardLayout() {
        const cards = document.querySelectorAll('.professor-card');
        const gridWidth = grid.offsetWidth;
        
        let columns;
        if (gridWidth >= 1200) {
            columns = 3;
        } else if (gridWidth >= 768) {
            columns = 2;
        } else {
            columns = 1;
        }
        
        document.body.setAttribute('data-card-columns', columns);
    }
    
    adjustCardLayout();
    
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(adjustCardLayout, 250);
    });
}

function initAccessibility() {
    const cards = document.querySelectorAll('.professor-card');
    
    cards.forEach(card => {
        const subjectTitle = card.querySelector('.subject-title');
        const professorName = card.querySelector('.professor-name');
        
        if (subjectTitle && professorName) {
            const ariaLabel = `${subjectTitle.textContent} - Instructor: ${professorName.textContent}. Click for more details.`;
            card.setAttribute('aria-label', ariaLabel);
        }
    });
    
    const emailLinks = document.querySelectorAll('.professor-email');
    emailLinks.forEach(link => {
        const professorName = link.closest('.professor-info')?.querySelector('.professor-name')?.textContent;
        if (professorName) {
            link.setAttribute('aria-label', `Email ${professorName}`);
        }
    });
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function initSearch() {
    const searchInput = document.querySelector('#professorSearch');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.professor-card');
        
        cards.forEach(card => {
            const subjectName = card.querySelector('.subject-title')?.textContent.toLowerCase() || '';
            const subjectCode = card.querySelector('.subject-code')?.textContent.toLowerCase() || '';
            const professorName = card.querySelector('.professor-name')?.textContent.toLowerCase() || '';
            
            const matches = subjectName.includes(searchTerm) || 
                          subjectCode.includes(searchTerm) || 
                          professorName.includes(searchTerm);
            
            if (matches) {
                card.style.display = 'flex';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                }, 10);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });
        
        const visibleCards = Array.from(cards).filter(card => 
            card.style.display !== 'none'
        );
        
        showNoResultsMessage(visibleCards.length === 0);
    });
}

function showNoResultsMessage(show) {
    let noResults = document.querySelector('.no-results-message');
    
    if (show && !noResults) {
        noResults = document.createElement('div');
        noResults.className = 'no-results-message';
        noResults.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <h3>No professors found</h3>
                <p>Try adjusting your search terms</p>
            </div>
        `;
        document.querySelector('.professors-content').appendChild(noResults);
    } else if (!show && noResults) {
        noResults.remove();
    }
}

document.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG' && e.target.closest('.professor-avatar')) {
        const defaultAvatar = (window.BASE_URL || '') + 'assets/images/default-avatar.png';
        if (e.target.src !== defaultAvatar) {
            e.target.src = defaultAvatar;
        }
    }
}, true);

function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('.professor-avatar img, .modal-avatar-large img');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
}

window.ProfessorsPage = {
    scrollToTop,
    initSearch,
    openProfessorModal
};