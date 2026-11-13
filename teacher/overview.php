<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity - Online Grading System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>teacher-pages/overview.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/teacher-nav.php'; ?>
        
        <div class="main-content">
            <div class="dashboard-content">
                <div class="audit-trail-container">
                    
                    
                    <!-- Filter Section -->
                    <div class="audit-filter-card">
                        <form id="filterForm" class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-search"></i>
                                    Search
                                </label>
                                <input type="text" 
                                       id="searchInput" 
                                       class="filter-input" 
                                       placeholder="Search by user or action...">
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-filter"></i>
                                    Action Type
                                </label>
                                <select id="actionTypeFilter" class="filter-select">
                                    <option value="">All Actions</option>
                                    <option value="login">Login</option>
                                    <option value="logout">Logout</option>
                                    <option value="create">Create</option>
                                    <option value="update">Update</option>
                                    <option value="delete">Delete</option>
                                    <option value="join">Join</option>
                                    <option value="unenroll">Unenroll</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-calendar"></i>
                                    Date
                                </label>
                                <input type="date" 
                                       id="dateFilter" 
                                       class="filter-input">
                            </div>

                            <div class="filter-group">
                                <button type="button" id="btnApplyFilter" class="btn-filter">
                                    <i class="fas fa-search"></i>
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Table Section -->
                    <div class="audit-table-card">
                        <div class="table-header">
                            <h2 class="table-title">
                                Activity Logs
                                <span class="table-count" id="logCount">0</span>
                            </h2>
                        </div>

                        <div class="audit-table-wrapper" style="position: relative;">
                            <div id="loadingOverlay" class="loading-overlay" style="display: none;">
                                <div class="loader"></div>
                            </div>

                            <table class="audit-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody id="auditTableBody">
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <div class="empty-title">No activity found</div>
                                            <div class="empty-message">Activity will appear here</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-wrapper" id="paginationWrapper" style="display: none;">
                            <div class="pagination-info" id="paginationInfo">
                                Showing 1-10 of 100 entries
                            </div>
                            <div class="pagination" id="pagination">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo JS_PATH; ?>main.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo JS_PATH; ?>teacher-pages/overview.js?v=<?php echo time(); ?>"></script>
</body>
</html>