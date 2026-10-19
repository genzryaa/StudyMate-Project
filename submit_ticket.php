<?php
require_once 'includes/load.php';
require_user();

if (!is_logged_in()) {
    redirect('auth/auth.php');
}

if (isset($_POST['send_ticket'])) {
    
    $user_id = $_SESSION['user_id'];
    $subject = clean_input($_POST['subject']);
    $message = clean_input($_POST['message']);

    $sql = "INSERT INTO support_tickets (user_id, subject, message) VALUES ('$user_id', '$subject', '$message')";

    if ($con->query($sql)) {
        redirect('help.php?msg=ticket_sent');
    } else {
        die("Error: " . $con->error);
    }

} else {
    redirect('help.php');
}
?>