<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (isset($_GET['confirmed']) && $_GET['confirmed'] === '1') {
    
    if (isset($_SESSION['user_id']) && function_exists('logAudit')) {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['full_name'] ?? 'Unknown User';
        
        try {
            logAudit(
                'User logged out',              
                'logout',                      
                'users',                        
                $user_id,                       
                $user_name . ' logged out from the system',  
                $user_id                        
            );
        } catch (Exception $e) {
            error_log("Logout logging error: " . $e->getMessage());
        }
    }
    
    session_destroy();
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Logging out...</title>
        <script>
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith("profile_") || key === "current_user_id") {
                    localStorage.removeItem(key);
                }
            });
            
            sessionStorage.clear();
            
            window.location.replace("<?php echo BASE_URL; ?>auth/login.php");
        </script>
    </head>
    <body></body>
    </html>
    <?php
    exit;
}

if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: " . BASE_URL);
}
exit;
?>