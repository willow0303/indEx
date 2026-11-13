function createCircles() {
    const container = document.getElementById('circlesContainer');
    if (!container) return;
    
    const colors = ['#2575fc', '#6a11cb', '#ff7e5f', '#feb47b'];

    for (let i = 0; i < 25; i++) {
        const circle = document.createElement('div');
        circle.classList.add('circle');

        const size = Math.random() * 80 + 20; 
        const left = Math.random() * 100; 
        const top = Math.random() * 100; 
        const color = colors[Math.floor(Math.random() * colors.length)];
        const delay = Math.random() * 15; 
        const duration = Math.random() * 10 + 15;

        circle.style.width = `${size}px`;
        circle.style.height = `${size}px`;
        circle.style.left = `${left}%`;
        circle.style.top = `${top}%`;
        circle.style.background = color;
        circle.style.animationDelay = `${delay}s`;
        circle.style.animationDuration = `${duration}s`;

        container.appendChild(circle);
    }
}

const studentBtn = document.getElementById('studentBtn');
const professorBtn = document.getElementById('professorBtn');

if (studentBtn) {
    studentBtn.addEventListener('click', () => {
        studentBtn.classList.add('active');
        if (professorBtn) professorBtn.classList.remove('active');
        console.log('Student role selected');
    });
}

if (professorBtn) {
    professorBtn.addEventListener('click', () => {
        professorBtn.classList.add('active');
        if (studentBtn) studentBtn.classList.remove('active');
        console.log('Professor role selected');
    });
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    createCircles();
    
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.login-btn');
            submitBtn.textContent = 'Logging in...';
            submitBtn.disabled = true;
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});