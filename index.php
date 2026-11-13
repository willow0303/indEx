    <?php
    require_once 'includes/config.php';
    require_once 'includes/session.php';

    if (isLoggedIn()) {
        redirectToDashboard();
    }

    $flash_message = $_SESSION['flash_message'] ?? null;
    if ($flash_message) {
        unset($_SESSION['flash_message']); 
    }
    ?>
    <!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>indEx - Online Grading System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>final-main.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>landing.css?v=<?php echo time(); ?>">
</head>
    <body>

    <header class="navbar">
        <div class="container">
            <a href="index.php" class="school-logo-link">
                <img src="<?php echo BASE_URL; ?>assets/images/psu.png" alt="PSU Logo" class="school-logo-img">
            </a>
            <div class="navbar-title">indEx</div>
            <nav class="nav-menu">
                <a href="#about" class="nav-link">About Us</a>
                <a href="#features" class="nav-link">Features</a>
                <a href="auth/login.php" class="nav-btn">Login</a> 
            </nav>
            <button class="nav-toggle" aria-label="toggle navigation">
                <span class="hamburger"></span>
            </button>
        </div>
    </header>

    <?php if ($flash_message): ?>
    <div class="flash-message">
        <?php echo htmlspecialchars($flash_message); ?>
    </div>
    <?php endif; ?>

    <section class="hero">
        <div class="container">
            <div class="hero-text animate-on-scroll">
                <h1>indEx: The Centralized Hub for Academic Excellence.</h1>
                <p class="subheader">Transition from disjointed paper trails and spreadsheets to a fully integrated digital environment. indEx centralizes the entire grading ecosystem, offering powerful organization tools for instructors and providing students with immediate, secure access to their complete, verified performance data.</p>
                <a href="#features" class="btn btn-primary">Explore Features</a>
            </div>
            <div class="hero-img animate-on-scroll">
                <img src="<?php echo BASE_URL; ?>assets/images/hero.png" 
                    alt="Preview of the indEx application interface"
                    onerror="this.onerror=null;this.src='https://placehold.co/600x400/c3cfe2/7B2D26?text=Grades+Dashboard';">
            </div>
        </div>
    </section>

    <section class="why" id="about">
        <div class="container">
            <div class="section-intro animate-on-scroll">
                <h2>Ditching the Old Way: Why Go Digital?</h2>
                <p>
                   From paper grade books to fragmented spreadsheets and endless student emails, the old way is slow, disorganized, and lacks transparency. <strong>indEx</strong> digitizes the entire classroom workflow, giving instructors unmatched efficiency for tracking grades and students instant, clear insight into their performance.
                </p>
            </div>
            <div class="why-grid">
                <div class="card animate-on-scroll">
                    <h3>📈 Real-Time Insight</h3>
                    <p>Students see scores instantly. Instructors gain immediate clarity on class progress.</p>
                </div>
                <div class="card animate-on-scroll" data-delay="100">
                    <h3>🔒 Secure & Centralized</h3>
                    <p>A permanent, verified digital record. Eliminates paper trails and grade discrepancies.</p>
                </div>
                <div class="card animate-on-scroll" data-delay="200">
                    <h3>💻 Cross-Device Access</h3>
                    <p>Access and manage your data seamlessly from desktop, laptop, or mobile.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="role-based-section" id="features">
        <div class="container">
            <h2 class="animate-on-scroll">One App. Two Experiences.</h2>
            <div class="role-grid">
                <div class="role-card student animate-on-scroll">
                    <div class="role-icon">🧑‍🎓</div>
                    <h3>For Students</h3>
                    <p>Your complete classroom overview. Track your academic standing and see all grades (quizzes, activities, and exams) instantly, the moment they are posted.</p>
                    <ul>
                        <li>View all your scores in one place</li>
                        <li>Track your class standing (real-time)</li>
                        <li>See detailed score breakdowns</li>
                        <li>Access a permanent record of all scores</li>
                    </ul>
                    <a href="auth/register.php?role=student" class="primary-btn-outline">Sign Up as a Student</a>
                </div>
                <div class="role-card professor animate-on-scroll" data-delay="100">
                    <div class="role-icon">🧑‍🏫</div>
                    <h3>For Instructors</h3>
                    <p>Your digital gradebook and analytics tool. Post scores, manage class lists, and automatically generate final grades with administrative ease.</p>
                    <ul>
                        <li>Manage your gradebook digitally</li>
                        <li>Post scores for quizzes & activities</li>
                        <li>Automate all final grade calculations</li>
                        <li>Ensure student clarity and reduce inquiries</li>
                    </ul>
                    <a href="auth/register.php?role=teacher" class="primary-btn-outline">Sign Up as a Professor</a>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-showcase">
    <div class="container">
        <div class="dashboard-text animate-on-scroll">
            <h2>Your Whole Classroom at a Glance</h2>
            <p>
                <strong>indEx</strong> centralizes all course data within a single, powerful dashboard. <strong>Instructors</strong> gain complete administrative control and gradebook management, while <strong>students</strong> receive an immediate, clear overview of their progress and verified scores.<br>
                The result: streamlined workflow and absolute clarity for everyone.
            </p>
        </div>
        <div class="dashboard-image animate-on-scroll" data-delay="100">
            <img src="<?php echo BASE_URL; ?>assets/images/mockUp.png" 
                alt="Dashboard mockup showing gradebook interface and student progress charts"
                onerror="this.onerror=null;this.src='https://placehold.co/1200x600/f9efef/7B2D26?text=Dashboard+Preview';">
        </div>
    </div>
</section>

   <section class="classroom-comparison-section">
    <div class="container animate-on-scroll">
        <h2>Disconnected vs. Centralized: The indEx Difference</h2>
        <div class="comparison-grid">
            <div class="disconnected-column">
                <h3>The Fragmented Workflow... ✕</h3>
                <ul>
                    <li>Stacks of quiz papers to record.</li>
                    <li>Frequent, repetitive grade inquiries.</li>
                    <li>Student loss or misplacement of graded papers.</li>
                    <li>Manual, error-prone grade computation.</li>
                    <li>No central source of truth.</li>
                </ul>
            </div>
            <div class="connected-column">
                <h3>The indEx Advantage! ✓</h3>
                <ul>
                    <li>Post grades in seconds, from anywhere.</li>
                    <li>Real-time progress tracking (24/7).</li>
                    <li>A secure, permanent digital record.</li>
                    <li>Automatic grade calculation.</li>
                    <li>Single source of truth for all academic scores.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

    <section class="cta-section">
    <div class="container animate-on-scroll">
        <h2>End the Confusion. Start with Clarity.</h2>
        <p>
            Ready to transition to a seamless, centralized digital gradebook? <strong>indEx</strong> provides absolute transparency and efficiency for students and instructors alike. <strong>Sign up is free and takes less than one minute.</strong>
        </p>
        <a href="auth/login.php" class="primary-btn">Start Your Free Account</a>
    </div>
</section>
    <footer class="footer">
    <span>© 2025 Pampanga State University. All rights reserved.</span>
    <a href="teacher/faq.php" class="footer-link">FAQ</a>
    </footer>

    <script src="<?php echo JS_PATH; ?>landing.js?v=<?php echo time(); ?>"></script>

    </body>
    </html>
