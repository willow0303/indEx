<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];

$archived_classes = [];

// Get archived classes with student count
try {
    $stmt = $conn->prepare("
        SELECT 
            c.*,
            COUNT(DISTINCT e.enrollment_id) as student_count,
            COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.enrollment_id END) as active_students
        FROM classes c
        LEFT JOIN enrollments e ON c.class_id = e.class_id
        WHERE c.teacher_id = ? AND c.status = 'archived'
        GROUP BY c.class_id
        ORDER BY c.updated_at DESC
    ");
    $stmt->execute([$teacher_id]);
    $archived_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Archive Error: " . $e->getMessage());
    $archived_classes = [];
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive - indEx</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>navigation.css">
    
    <style>
    .archive-page {
        padding: 32px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .archive-header {
        margin-bottom: 32px;
    }

    .archive-title {
        font-size: 32px;
        font-weight: 800;
        color: #333;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .archive-title i {
        color: #7b2d26;
    }

    .archive-subtitle {
        font-size: 15px;
        color: #666;
        margin: 0;
    }

    .archive-section {
        background: white;
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid #f0f0f0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #7b2d26;
    }

    .section-count {
        background: linear-gradient(135deg, #7b2d26 0%, #a74c42 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }

    .archive-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    .archive-card {
        background: linear-gradient(135deg, #fef3f4 0%, #fff 100%);
        border: 2px solid #f0f0f0;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .archive-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #7b2d26, #a74c42);
    }

    .archive-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(123, 45, 38, 0.15);
        border-color: #7b2d26;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .card-title-group {
        flex: 1;
    }

    .card-class-name {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        margin: 0 0 4px 0;
    }

    .card-subject {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    .card-section-badge {
        background: #7b2d26;
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .card-info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
    }

    .card-info-row i {
        width: 20px;
        color: #7b2d26;
    }

    .card-footer {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        gap: 8px;
    }

    .btn-restore {
        flex: 1;
        padding: 10px 16px;
        background: linear-gradient(135deg, #7b2d26 0%, #a74c42 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-restore:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(123, 45, 38, 0.3);
    }

    .btn-delete {
        padding: 10px 16px;
        background: transparent;
        color: #ef4444;
        border: 2px solid #ef4444;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        font-size: 64px;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-title {
        font-size: 20px;
        font-weight: 700;
        color: #333;
        margin: 0 0 8px 0;
    }

    .empty-text {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    /* Custom Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        animation: fadeIn 0.2s ease;
    }

    .modal-overlay.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        padding: 32px;
        max-width: 480px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    .modal-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 28px;
    }

    .modal-icon.restore {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        color: #2e7d32;
    }

    .modal-icon.delete {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #c62828;
    }

    .modal-title {
        font-size: 24px;
        font-weight: 800;
        color: #333;
        text-align: center;
        margin: 0 0 12px 0;
    }

    .modal-message {
        font-size: 15px;
        color: #666;
        text-align: center;
        line-height: 1.6;
        margin: 0 0 24px 0;
    }

    .modal-class-info {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
        border-left: 4px solid #7b2d26;
    }

    .modal-class-name {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin: 0 0 4px 0;
    }

    .modal-class-detail {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    .modal-warning {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 24px;
        display: flex;
        align-items: start;
        gap: 12px;
    }

    .modal-warning i {
        color: #f57c00;
        font-size: 20px;
        margin-top: 2px;
    }

    .modal-warning-text {
        font-size: 13px;
        color: #856404;
        font-weight: 600;
        line-height: 1.5;
    }

    .modal-buttons {
        display: flex;
        gap: 12px;
    }

    .modal-btn {
        flex: 1;
        padding: 14px 24px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .modal-btn-cancel {
        background: #f0f0f0;
        color: #666;
    }

    .modal-btn-cancel:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .modal-btn-confirm {
        background: linear-gradient(135deg, #7b2d26 0%, #a74c42 100%);
        color: white;
    }

    .modal-btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(123, 45, 38, 0.4);
    }

    .modal-btn-delete {
        background: linear-gradient(135deg, #c62828 0%, #ef5350 100%);
        color: white;
    }

    .modal-btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(198, 40, 40, 0.4);
    }

    .modal-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    @media (max-width: 768px) {
        .archive-page {
            padding: 20px;
        }

        .archive-grid {
            grid-template-columns: 1fr;
        }

        .modal-content {
            padding: 24px;
        }

        .modal-buttons {
            flex-direction: column;
        }
    }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/teacher-nav.php'; ?>
        
        <div class="main-content">
            <div class="content-wrapper">
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                        <i class="fas fa-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="archive-page">
                    <div class="archive-header">
                        <h1 class="archive-title">
                            <i class="fas fa-archive"></i>
                            Archive
                        </h1>
                        <p class="archive-subtitle">Manage archived classes</p>
                    </div>

                    <!-- Archived Classes Section -->
                    <div class="archive-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-box-archive"></i>
                                Archived Classes
                            </h2>
                            <span class="section-count"><?php echo count($archived_classes); ?> Archived</span>
                        </div>

                        <?php if (count($archived_classes) > 0): ?>
                            <div class="archive-grid">
                                <?php foreach ($archived_classes as $class): ?>
                                    <div class="archive-card">
                                        <div class="card-header">
                                            <div class="card-title-group">
                                                <h3 class="card-class-name"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                                                <p class="card-subject"><?php echo htmlspecialchars($class['subject']); ?></p>
                                            </div>
                                            <span class="card-section-badge">Section <?php echo htmlspecialchars($class['section']); ?></span>
                                        </div>

                                        <div class="card-body">
                                            <div class="card-info-row">
                                                <i class="fas fa-key"></i>
                                                <span>Code: <strong><?php echo htmlspecialchars($class['class_code']); ?></strong></span>
                                            </div>
                                            <div class="card-info-row">
                                                <i class="fas fa-users"></i>
                                                <span><?php echo $class['student_count']; ?> student<?php echo $class['student_count'] != 1 ? 's' : ''; ?> enrolled</span>
                                            </div>
                                            <div class="card-info-row">
                                                <i class="fas fa-calendar"></i>
                                                <span>Archived: <?php echo formatDate($class['updated_at'], 'M j, Y'); ?></span>
                                            </div>
                                        </div>

                                        <div class="card-footer">
                                            <button class="btn-restore" onclick="showRestoreModal(<?php echo $class['class_id']; ?>, '<?php echo htmlspecialchars($class['class_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($class['section'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($class['subject'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-undo"></i>
                                                Restore
                                            </button>
                                            <button class="btn-delete" onclick="showDeleteModal(<?php echo $class['class_id']; ?>, '<?php echo htmlspecialchars($class['class_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($class['section'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($class['subject'], ENT_QUOTES); ?>')" title="Permanently delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <h3 class="empty-title">No Archived Classes</h3>
                                <p class="empty-text">You don't have any archived classes yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <!-- Restore Modal -->
    <div id="restoreModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon restore">
                <i class="fas fa-undo"></i>
            </div>
            <h2 class="modal-title">Restore Class?</h2>
            <p class="modal-message">This will make the class active again and students will be able to access it.</p>
            
            <div class="modal-class-info">
                <p class="modal-class-name" id="restoreClassName"></p>
                <p class="modal-class-detail" id="restoreClassDetail"></p>
            </div>

            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('restoreModal')">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button class="modal-btn modal-btn-confirm" id="confirmRestoreBtn">
                    <i class="fas fa-check"></i>
                    Restore Class
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon delete">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h2 class="modal-title">Permanently Delete Class?</h2>
            <p class="modal-message">This action cannot be undone.</p>
            
            <div class="modal-class-info">
                <p class="modal-class-name" id="deleteClassName"></p>
                <p class="modal-class-detail" id="deleteClassDetail"></p>
            </div>

            <div class="modal-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="modal-warning-text">
                    All grades, attendance, and enrollment records will be permanently deleted.
                </div>
            </div>

            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal('deleteModal')">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button class="modal-btn modal-btn-delete" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i>
                    Delete Permanently
                </button>
            </div>
        </div>
    </div>

    <script>
    let currentClassId = null;

    function showRestoreModal(classId, className, section, subject) {
        currentClassId = classId;
        document.getElementById('restoreClassName').textContent = className;
        document.getElementById('restoreClassDetail').textContent = `${subject} - Section ${section}`;
        document.getElementById('restoreModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function showDeleteModal(classId, className, section, subject) {
        currentClassId = classId;
        document.getElementById('deleteClassName').textContent = className;
        document.getElementById('deleteClassDetail').textContent = `${subject} - Section ${section}`;
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
        currentClassId = null;
    }

    // Close modal when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('restoreModal');
            closeModal('deleteModal');
        }
    });

    // Restore functionality
    document.getElementById('confirmRestoreBtn').addEventListener('click', function() {
        if (!currentClassId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';

        fetch('<?php echo BASE_URL; ?>api/teacher/restore-section-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'class_id=' + currentClassId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to restore section'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Restore Class';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Restore Class';
        });
    });

    // Delete functionality
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!currentClassId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        fetch('<?php echo BASE_URL; ?>api/teacher/delete-section-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'class_id=' + currentClassId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete section'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash"></i> Delete Permanently';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Delete Permanently';
        });
    });
    </script>
</body>
</html>