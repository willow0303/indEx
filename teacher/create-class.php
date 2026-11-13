<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireTeacher();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class - indEx</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>dashboard.css">
    
    <style>
    :root {
        --maroon: #7b2d26;
        --maroon-dark: #5a1f18;
        --maroon-light: #a74c42;
        --cream: #f9efef;
        --gray-dark: #333333;
        --gray-medium: #666666;
        --gray-light: #f0f0f0;
        --red: #ef4444;
        --shadow: 0 2px 8px rgba(123, 45, 38, 0.1);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, var(--cream) 0%, #faf5f3 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 64px);
    }

    .create-class-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
        width: 100%;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--maroon);
        text-decoration: none;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: white;
        border: 2px solid var(--maroon);
        transition: all 0.3s;
        margin-bottom: 1rem;
        width: fit-content;
    }

    .back-button:hover {
        background: var(--maroon);
        color: white;
    }

    .content-layout {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 1.5rem;
        flex: 1;
    }

    /* Form Card - Left */
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        overflow: hidden;
        height: fit-content;
    }

    .card-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-light) 100%);
        color: white;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .card-header i {
        font-size: 1.75rem;
    }

    .card-header h1 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-dark);
        margin-bottom: 0.375rem;
    }

    .label-required {
        color: var(--red);
    }

    .input-wrapper {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-medium);
        font-size: 1rem;
    }

    .form-input {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-dark);
        background: var(--cream);
        border: 2px solid var(--gray-light);
        border-radius: 8px;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .form-input:focus {
        outline: none;
        background: white;
        border-color: var(--maroon);
        box-shadow: 0 0 0 3px rgba(123, 45, 38, 0.1);
    }

    /* Schedule Section */
    .schedule-section {
        margin: 1rem 0;
        padding: 1rem;
        background: linear-gradient(135deg, #fef3f4 0%, #fff 100%);
        border: 2px dashed var(--maroon-light);
        border-radius: 8px;
    }

    .schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .schedule-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--maroon);
    }

    .btn-add {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 0.875rem;
        background: var(--maroon);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-add:hover {
        background: var(--maroon-light);
    }

    .schedule-item {
        background: white;
        border: 2px solid var(--gray-light);
        border-radius: 8px;
        padding: 0.875rem;
        margin-bottom: 0.75rem;
    }

    .schedule-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .schedule-badge {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--maroon);
    }

    .btn-remove {
        padding: 0.25rem 0.5rem;
        background: transparent;
        color: var(--red);
        border: 1px solid var(--red);
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.75rem;
    }

    .btn-remove:hover {
        background: var(--red);
        color: white;
    }

    .schedule-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .schedule-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-dark);
        margin-bottom: 0.25rem;
        display: block;
    }

    .schedule-input,
    .schedule-select {
        width: 100%;
        padding: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
        background: var(--cream);
        border: 2px solid var(--gray-light);
        border-radius: 6px;
        font-family: 'Poppins', sans-serif;
    }

    .schedule-input:focus,
    .schedule-select:focus {
        outline: none;
        background: white;
        border-color: var(--maroon);
    }

    .form-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-light);
    }

    .btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--maroon);
        color: white;
        box-shadow: var(--shadow);
    }

    .btn-primary:hover:not(:disabled) {
        background: var(--maroon-light);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--gray-light);
        color: var(--gray-dark);
    }

    .btn-secondary:hover {
        background: var(--gray-medium);
        color: white;
    }

    /* Instructions Card - Right */
    .instructions-card {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        overflow: hidden;
        height: fit-content;
    }

    .instructions-header {
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-light) 100%);
        color: white;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .instructions-header i {
        font-size: 1.5rem;
    }

    .instructions-header h2 {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
    }

    .instructions-body {
        padding: 1.25rem;
    }

    .step-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--cream);
        border-radius: 8px;
        margin-bottom: 0.625rem;
        border: 2px solid var(--gray-light);
        transition: all 0.3s;
    }

    .step-item:hover {
        border-color: var(--maroon);
        transform: translateX(4px);
    }

    .step-number {
        width: 32px;
        height: 32px;
        background: var(--maroon);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 800;
        flex-shrink: 0;
    }

    .step-content h4 {
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--gray-dark);
        margin: 0 0 0.125rem 0;
    }

    .step-content p {
        font-size: 0.75rem;
        color: var(--gray-medium);
        margin: 0;
    }

    .alert {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-left: 4px solid;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-color: #10b981;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-color: var(--red);
    }

    .footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background-color: var(--maroon);
        color: white;
        font-size: 0.8125rem;
        margin-top: auto;
    }

    .footer p {
        margin: 0;
    }

    .footer a {
        color: white;
        text-decoration: none;
        transition: all 0.2s;
    }

    .footer a:hover {
        color: #f5d5c8;
    }

    @media (max-width: 1024px) {
        .content-layout {
            grid-template-columns: 1fr;
        }

        .instructions-card {
            order: -1;
        }
    }

    @media (max-width: 768px) {
        .create-class-wrapper {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .schedule-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .footer {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
            padding: 1rem;
        }
    }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/teacher-nav.php'; ?>
        
        <div class="main-content">
            <div class="create-class-wrapper">
                <a href="my-courses.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Courses</span>
                </a>

                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                        <i class="fas fa-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="content-layout">
                    <!-- Left: Form -->
                    <div class="form-card">
                        <div class="card-header">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <h1>Create New Class</h1>
                        </div>
                        
                        <div class="card-body">
                            <form action="<?php echo BASE_URL; ?>api/teacher/create-class-handler.php" method="POST" id="createClassForm">
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <span>Class Name</span>
                                        <span class="label-required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input 
                                            type="text" 
                                            name="class_name" 
                                            class="form-input" 
                                            placeholder="e.g., Computer Programming 101"
                                            required
                                            maxlength="100"
                                        >
                                        <i class="fas fa-book input-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <span>Subject</span>
                                        <span class="label-required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input 
                                            type="text" 
                                            name="subject" 
                                            class="form-input" 
                                            placeholder="e.g., Computer Science"
                                            required
                                            maxlength="100"
                                        >
                                        <i class="fas fa-book-open input-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <span>Section</span>
                                        <span class="label-required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input 
                                            type="text" 
                                            name="section" 
                                            class="form-input" 
                                            placeholder="e.g., Section A, 1-A"
                                            required
                                            maxlength="50"
                                        >
                                        <i class="fas fa-users input-icon"></i>
                                    </div>
                                </div>

                                <div class="schedule-section">
                                    <div class="schedule-header">
                                        <h3 class="schedule-title">Class Schedule (Optional)</h3>
                                        <button type="button" class="btn-add" id="addScheduleBtn">
                                            <i class="fas fa-plus"></i>
                                            Add
                                        </button>
                                    </div>

                                    <div id="schedulesContainer"></div>
                                </div>
                                
                                <div class="form-actions">
                                    <a href="my-courses.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                        <span>Cancel</span>
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-check"></i>
                                        <span>Create Class</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Instructions -->
                    <div class="instructions-card">
                        <div class="instructions-header">
                            <i class="fas fa-info-circle"></i>
                            <h2>How It Works</h2>
                        </div>
                        
                        <div class="instructions-body">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Enter Class Details</h4>
                                    <p>Fill in class name, subject, and section</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Add Schedules</h4>
                                    <p>Set up class meeting times (optional)</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Generate Class Code</h4>
                                    <p>System creates a unique code automatically</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <h4>Share with Students</h4>
                                    <p>Students join instantly with the code</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../includes/footer.php'; ?>
        </div>
    </div>
    
    <script>
    let scheduleCount = 0;
    
    document.addEventListener('DOMContentLoaded', function() {
        const addBtn = document.getElementById('addScheduleBtn');
        const container = document.getElementById('schedulesContainer');
        
        addBtn.onclick = function(e) {
            e.preventDefault();
            addSchedule();
        };
        
        function addSchedule() {
            scheduleCount++;
            
            const html = `
                <div class="schedule-item" data-schedule-id="${scheduleCount}">
                    <div class="schedule-item-header">
                        <span class="schedule-badge">Schedule ${scheduleCount}</span>
                        <button type="button" class="btn-remove" onclick="removeSchedule(${scheduleCount})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    
                    <div class="schedule-row">
                        <div>
                            <label class="schedule-label">Day</label>
                            <select name="schedules[${scheduleCount}][day]" class="schedule-select" required>
                                <option value="">Select</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="schedule-label">Start Time</label>
                            <input 
                                type="time" 
                                name="schedules[${scheduleCount}][start_time]" 
                                class="schedule-input"
                                required
                            >
                        </div>
                        
                        <div>
                            <label class="schedule-label">End Time</label>
                            <input 
                                type="time" 
                                name="schedules[${scheduleCount}][end_time]" 
                                class="schedule-input"
                                required
                            >
                        </div>
                    </div>
                    
                    <div>
                        <label class="schedule-label">Room (Optional)</label>
                        <input 
                            type="text" 
                            name="schedules[${scheduleCount}][room]" 
                            class="schedule-input"
                            placeholder="e.g., Room 301"
                            maxlength="100"
                        >
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }
        
        window.removeSchedule = function(id) {
            const item = document.querySelector(`[data-schedule-id="${id}"]`);
            if (item) {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-100%)';
                setTimeout(() => item.remove(), 300);
            }
        };
        
        document.getElementById('createClassForm').onsubmit = function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Creating...';
        };
    });
    </script>
    
    <script src="<?php echo JS_PATH; ?>main.js"></script>
</body>
</html>