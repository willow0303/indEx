document.addEventListener('DOMContentLoaded', function() {
    initHorizontalScroll();
    initCopyButtons();
    initAlertAutoHide();
});

function initHorizontalScroll() {
    const wrapper = document.getElementById('sectionsWrapper');
    const scrollLeft = document.getElementById('scrollLeft');
    const scrollRight = document.getElementById('scrollRight');
    
    if (!wrapper || !scrollLeft || !scrollRight) return;
    
    function checkScroll() {
        const isScrollable = wrapper.scrollWidth > wrapper.clientWidth;
        
        if (isScrollable) {
            scrollLeft.style.display = wrapper.scrollLeft > 5 ? 'flex' : 'none';
            const atEnd = wrapper.scrollLeft >= (wrapper.scrollWidth - wrapper.clientWidth - 5);
            scrollRight.style.display = atEnd ? 'none' : 'flex';
        } else {
            scrollLeft.style.display = 'none';
            scrollRight.style.display = 'none';
        }
    }
    
    scrollLeft.addEventListener('click', function() {
        const cardWidth = wrapper.querySelector('.section-card')?.offsetWidth || 300;
        const gap = 16;
        wrapper.scrollBy({
            left: -(cardWidth + gap),
            behavior: 'smooth'
        });
    });
    
    scrollRight.addEventListener('click', function() {
        const cardWidth = wrapper.querySelector('.section-card')?.offsetWidth || 300;
        const gap = 16;
        wrapper.scrollBy({
            left: cardWidth + gap,
            behavior: 'smooth'
        });
    });
    
    wrapper.addEventListener('scroll', checkScroll);
    checkScroll();
    window.addEventListener('resize', checkScroll);
    
    let isDown = false;
    let startX;
    let scrollLeftPos;
    
    wrapper.addEventListener('mousedown', function(e) {
        if (e.target.closest('button') || e.target.closest('a')) return;
        
        isDown = true;
        wrapper.style.cursor = 'grabbing';
        wrapper.style.userSelect = 'none';
        startX = e.pageX - wrapper.offsetLeft;
        scrollLeftPos = wrapper.scrollLeft;
    });
    
    wrapper.addEventListener('mouseleave', function() {
        isDown = false;
        wrapper.style.cursor = 'grab';
        wrapper.style.userSelect = '';
    });
    
    wrapper.addEventListener('mouseup', function() {
        isDown = false;
        wrapper.style.cursor = 'grab';
        wrapper.style.userSelect = '';
    });
    
    wrapper.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - wrapper.offsetLeft;
        const walk = (x - startX) * 2; 
        wrapper.scrollLeft = scrollLeftPos - walk;
    });
    
    // Touch support for mobile
    let touchStartX = 0;
    let touchScrollLeft = 0;
    
    wrapper.addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].pageX;
        touchScrollLeft = wrapper.scrollLeft;
    });
    
    wrapper.addEventListener('touchmove', function(e) {
        const touchX = e.touches[0].pageX;
        const walk = (touchStartX - touchX) * 1.5;
        wrapper.scrollLeft = touchScrollLeft + walk;
    });
}

function initCopyButtons() {
    document.addEventListener('click', function(e) {
        const copyBtn = e.target.closest('.btn-copy-code');
        if (copyBtn) {
            e.preventDefault();
            e.stopPropagation();
            const code = copyBtn.dataset.code;
            copyToClipboard(code, copyBtn);
        }
    });
}

function copyToClipboard(text, button) {
    if (!text) {
        console.error('No text to copy');
        return;
    }
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => {
                showCopySuccess(button);
            })
            .catch(err => {
                console.error('Failed to copy:', err);
                fallbackCopy(text, button);
            });
    } else {
        fallbackCopy(text, button);
    }
}

function showCopySuccess(button) {
    const originalHTML = button.innerHTML;
    const originalBg = button.style.background;
    
    // Change to success state
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.style.background = '#10b981';
    button.style.transform = 'scale(1.05)';
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.style.background = originalBg;
        button.style.transform = '';
    }, 2000);
}

function fallbackCopy(text, button) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(button);
        } else {
            alert('Failed to copy. Please copy manually: ' + text);
        }
    } catch (err) {
        console.error('Fallback copy failed:', err);
        alert('Failed to copy. Please copy manually: ' + text);
    }
    
    document.body.removeChild(textArea);
}

function initAlertAutoHide() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Export functions if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initHorizontalScroll,
        initCopyButtons,
        copyToClipboard
    };
}