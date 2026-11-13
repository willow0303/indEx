<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$limit = 100;

$logs = [];
$error_message = '';

try {
    $query = "SELECT 
        log_id,
        action,
        action_type,
        table_affected,
        record_id,
        description,
        ip_address,
        created_at
    FROM audit_logs 
    WHERE user_id = :user_id
    ORDER BY created_at DESC 
    LIMIT :limit";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':user_id', $teacher_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Audit Trail Error: " . $e->getMessage());
    $error_message = "Unable to load audit logs. Please contact support.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - Online Grading System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>navigation.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            color: #2c2c2c;
            display: flex;
            flex-direction: column;
        }

        .dashboard-wrapper {
            display: flex;
            flex: 1;
            min-height: 0;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .error-alert {
            background: #fff;
            border-left: 4px solid #dc3545;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #dc3545;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .error-alert i {
            font-size: 18px;
        }

        .activities-section {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }

        .section-header {
            padding: 24px 28px;
            background: #ffffff;
            color: #2c2c2c;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .section-header h2 {
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header i {
            font-size: 16px;
        }

        .activities-list {
            max-height: calc(100vh - 350px);
            overflow-y: auto;
        }

        .activities-list::-webkit-scrollbar {
            width: 8px;
        }

        .activities-list::-webkit-scrollbar-track {
            background: #f5f5f5;
        }

        .activities-list::-webkit-scrollbar-thumb {
            background: #7B2D26;
            border-radius: 4px;
        }

        .activities-list::-webkit-scrollbar-thumb:hover {
            background: #5a1f1a;
        }

        .activity-item {
            padding: 20px 28px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 16px;
            transition: all 0.2s;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: #fafafa;
        }

        .activity-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .activity-icon.create {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            color: #2e7d32;
        }

        .activity-icon.update {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #f57c00;
        }

        .activity-icon.delete {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 6px;
        }

        .activity-title {
            font-size: 13px;
            font-weight: 600;
            color: #2c2c2c;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .activity-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-badge.create {
            background: #2e7d32;
            color: white;
        }

        .activity-badge.update {
            background: #f57c00;
            color: white;
        }

        .activity-badge.delete {
            background: #c62828;
            color: white;
        }

        .activity-time {
            font-size: 11px;
            color: #999;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .activity-time i {
            font-size: 10px;
        }

        .activity-description {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .activity-meta {
            display: flex;
            gap: 16px;
            font-size: 11px;
            color: #999;
        }

        .activity-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .activity-meta i {
            font-size: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }

        .empty-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-icon i {
            font-size: 35px;
            color: #bbb;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 13px;
            color: #999;
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 24px 16px;
            }

            .section-header {
                padding: 18px 20px;
            }

            .section-header h2 {
                font-size: 14px;
            }

            .activity-item {
                padding: 16px 20px;
            }

            .activity-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .activity-meta {
                flex-direction: column;
                gap: 8px;
            }

            .activities-list {
                max-height: calc(100vh - 200px);
            }
        }

        @media (max-width: 480px) {
            .activity-icon {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }

            .activity-title {
                font-size: 12px;
            }

            .activity-description {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/teacher-nav.php'; ?>
        
        <div class="main-content">
            <?php if (!empty($error_message)): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <div class="activities-section">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-list-ul"></i>
                        Recent Activities
                    </h2>
                </div>

                <div class="activities-list">
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php 
                                $action = strtoupper($log['action_type'] ?? '');
                                $icon_class = $action == 'CREATE' ? 'create' : ($action == 'UPDATE' ? 'update' : ($action == 'DELETE' ? 'delete' : 'update'));
                                $icon = $action == 'CREATE' ? 'plus' : ($action == 'UPDATE' ? 'edit' : ($action == 'DELETE' ? 'trash' : 'circle'));
                            ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $icon_class; ?>">
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-header">
                                        <div class="activity-title">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                            <?php if ($action): ?>
                                                <span class="activity-badge <?php echo $icon_class; ?>">
                                                    <?php echo $action; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-time">
                                            <i class="far fa-clock"></i>
                                            <?php echo date('M d, Y • g:i A', strtotime($log['created_at'])); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($log['description']) && !empty($log['description'])): ?>
                                        <div class="activity-description">
                                            <?php echo htmlspecialchars($log['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="activity-meta">
                                        <?php if (isset($log['table_affected']) && !empty($log['table_affected'])): ?>
                                            <span>
                                                <i class="fas fa-table"></i>
                                                <?php echo htmlspecialchars(ucfirst($log['table_affected'])); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($log['record_id']) && !empty($log['record_id'])): ?>
                                            <span>
                                                <i class="fas fa-hashtag"></i>
                                                ID: <?php echo htmlspecialchars($log['record_id']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($log['ip_address']) && !empty($log['ip_address'])): ?>
                                            <span>
                                                <i class="fas fa-network-wired"></i>
                                                <?php echo htmlspecialchars($log['ip_address']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h3>No Activities Yet</h3>
                            <p>Your activity history will appear here once you start grading students or marking attendance.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="<?php echo JS_PATH; ?>main.js"></script>
</body>
</html>