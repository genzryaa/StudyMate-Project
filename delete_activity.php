<?php
require_once 'includes/load.php';
require_user(); // Ensure user is logged in

if(isset($_GET['id']) && isset($_GET['type'])) {
    
    $id = intval($_GET['id']);
    $type = $_GET['type'];
    $user_id = $_SESSION['user_id'];
    $table = '';

    // Determine which table to delete from
    switch($type) {
        case 'tasks':
            $table = 'tasks';
            break;
        case 'classes':
            $table = 'classes';
            break;
        case 'exams':
            $table = 'exams';
            break;
        case 'extra':
            $table = 'extra_activities';
            break;
        default:
            redirect('activities.php');
    }

    if(!empty($table)) {
        // Execute Delete
        // We include 'AND user_id' to ensure users can only delete their own data
        $sql = "DELETE FROM $table WHERE id = '$id' AND user_id = '$user_id'";
        
        if($con->query($sql)) {
            redirect("activities.php?view=$type&msg=deleted");
        } else {
            die("Database Error: " . $con->error);
        }
    }
} else {
    redirect('activities.php');
}
?>