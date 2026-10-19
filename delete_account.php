<?php
require_once 'includes/load.php';
require_user();

// 1. Check if user is logged in
if (!is_logged_in()) {
    redirect('auth/auth.php');
}

// 2. Get the User ID
$user_id = $_SESSION['user_id'];

// 3. Delete the user
// Note: In a real project, you might also want to delete their tasks/activities first
// but for now, we just delete the user.
$sql = "DELETE FROM users WHERE id = '$user_id'";

if ($con->query($sql)) {
    // 4. Destroy the session
    session_destroy();
    
    // 5. Redirect to Auth with a "Gone" message
    redirect('auth/auth.php?msg=account_deleted');
} else {
    echo "Error deleting account: " . $con->error;
}
?>