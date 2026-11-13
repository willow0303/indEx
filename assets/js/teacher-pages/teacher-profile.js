(function() {
    'use strict';

    function init() {
        setupProfileImageUpdates();
        setupAlertAutoHide();
        checkForProfileUpdates();
    }

    function setupProfileImageUpdates() {
        updateProfileImageFromStorage();

        window.addEventListener('storage', function(e) {
            if (e.key === 'profile_picture_updated' || e.key === 'profile_name_updated') {
                updateProfileImageFromStorage();
                updateProfileNameFromStorage();
            }
        });

        window.addEventListener('profileUpdated', function() {
            updateProfileImageFromStorage();
            updateProfileNameFromStorage();
        });
    }

    function updateProfileImageFromStorage() {
        const userId = getCurrentUserId();
        const profilePicUrl = localStorage.getItem('profile_picture_updated_' + userId) || 
                             localStorage.getItem('profile_picture_updated');
        
        if (profilePicUrl) {
            const timestamp = localStorage.getItem('profile_picture_timestamp_' + userId) || 
                            localStorage.getItem('profile_picture_timestamp');
            
            // Check if update is recent (within last 5 minutes)
            if (timestamp) {
                const updateTime = parseInt(timestamp);
                const now = Date.now();
                const fiveMinutes = 5 * 60 * 1000;
                
                if (now - updateTime < fiveMinutes) {
                    const profileImage = document.getElementById('profileImage');
                    if (profileImage) {
                        profileImage.src = profilePicUrl + '?t=' + new Date().getTime();
                    }
                }
            }
        }
    }

    function updateProfileNameFromStorage() {
        const userId = getCurrentUserId();
        const profileName = localStorage.getItem('profile_name_updated_' + userId) || 
                           localStorage.getItem('profile_name_updated');
        
        if (profileName) {
            const timestamp = localStorage.getItem('profile_update_timestamp_' + userId) || 
                            localStorage.getItem('profile_update_timestamp');
            
            // Check if update is recent (within last 5 minutes)
            if (timestamp) {
                const updateTime = parseInt(timestamp);
                const now = Date.now();
                const fiveMinutes = 5 * 60 * 1000;
                
                if (now - updateTime < fiveMinutes) {
                    const profileNameElement = document.querySelector('.profile-name');
                    if (profileNameElement) {
                        const nameParts = profileName.trim().split(' ');
                        const firstName = nameParts[0] || '';
                        const lastName = nameParts[nameParts.length - 1] || '';
                        
                        let middleInitial = '';
                        if (nameParts.length > 2) {
                            const middleName = nameParts.slice(1, -1).join(' ');
                            middleInitial = middleName.charAt(0).toUpperCase() + '.';
                        }
                        
                        // Format as "Last, First M."
                        let formattedName = lastName + ', ' + firstName;
                        if (middleInitial) {
                            formattedName += ' ' + middleInitial;
                        }
                        
                        profileNameElement.textContent = formattedName;
                    }
                }
            }
        }
    }

    function getCurrentUserId() {
        const metaUser = document.querySelector('meta[name="user-id"]');
        if (metaUser) {
            return metaUser.content;
        }
        return 'user_' + Date.now();
    }

    function checkForProfileUpdates() {
        const currentUserId = getCurrentUserId();
        const storedUserId = localStorage.getItem('current_user_id');
        
        // If different user, clear old profile data
        if (storedUserId && storedUserId !== currentUserId) {
            clearOldProfileData();
        }
        
        localStorage.setItem('current_user_id', currentUserId);
    }

    function clearOldProfileData() {
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.startsWith('profile_picture_') || key.startsWith('profile_name_') || key.startsWith('profile_update_')) {
                localStorage.removeItem(key);
            }
        });
    }

    function setupAlertAutoHide() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(alert => {
            setTimeout(() => {
                fadeOutAlert(alert);
            }, 5000);
            
            alert.style.cursor = 'pointer';
            alert.addEventListener('click', function() {
                fadeOutAlert(this);
            });
        });
    }

    function fadeOutAlert(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        alert.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            alert.remove();
        }, 300);
    }

    function setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    function setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe stat cards and action buttons
        document.querySelectorAll('.stat-card, .action-button-compact').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'all 0.6s ease';
            observer.observe(element);
        });
    }

    function setupProfileImageHover() {
        const profileImage = document.querySelector('.profile-image-wrapper');
        
        if (profileImage) {
            profileImage.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05) rotate(2deg)';
            });
            
            profileImage.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1) rotate(0deg)';
            });
        }
    }

    /**
     * Initialize tooltips (if needed)
     */
    function initializeTooltips() {
        const elementsWithTitle = document.querySelectorAll('[title]');
        
        elementsWithTitle.forEach(element => {
            const title = element.getAttribute('title');
            if (title) {
                element.addEventListener('mouseenter', function(e) {
                    showTooltip(e, title);
                });
                
                element.addEventListener('mouseleave', function() {
                    hideTooltip();
                });
            }
        });
    }

    /**
     * Show custom tooltip
     */
    function showTooltip(event, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = text;
        tooltip.style.position = 'fixed';
        tooltip.style.background = 'rgba(0, 0, 0, 0.8)';
        tooltip.style.color = 'white';
        tooltip.style.padding = '8px 12px';
        tooltip.style.borderRadius = '6px';
        tooltip.style.fontSize = '13px';
        tooltip.style.zIndex = '10000';
        tooltip.style.pointerEvents = 'none';
        
        document.body.appendChild(tooltip);
        
        const rect = event.target.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    }

    function hideTooltip() {
        const tooltip = document.querySelector('.custom-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    // Initialize everything when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            init();
            setupSmoothScroll();
            setupScrollAnimations();
            setupProfileImageHover();
        });
    } else {
        init();
        setupSmoothScroll();
        setupScrollAnimations();
        setupProfileImageHover();
    }

})();