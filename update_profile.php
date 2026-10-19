<?php
require_once 'includes/load.php';
require_user();

// ... rest of code

// Check login
if (!is_logged_in()) {
    redirect('auth/auth.php');
}

if (isset($_POST['update_btn'])) {
    
    $user_id = $_SESSION['user_id'];
    $username = clean_input($_POST['username']);
    
    // 1. Handle Password Update
    // ... inside if (isset($_POST['update_btn'])) ...

    // 1. Handle Password Update
    $password_sql = ""; 
    
    if (!empty($_POST['new_password'])) {
        $pass = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        // --- START NEW CODE ---
        if (strlen($pass) < 8) {
            // Redirect with error if too short
            redirect('profile.php?error=pass_short');
            exit(); 
        }
        // --- END NEW CODE ---

        if ($pass !== $confirm) {
            redirect('profile.php?error=p_mismatch');
            exit(); // Don't forget exit() after redirects!
        }
        
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $password_sql = ", password = '$hashed'";
    }

    // 2. Handle Image Upload
    $image_sql = ""; // Default: No image change
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        
        $img_name = $_FILES['profile_pic']['name'];
        $img_tmp  = $_FILES['profile_pic']['tmp_name'];
        $img_ext  = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($img_ext, $allowed)) {
            // New name: user_ID.jpg (e.g., user_5.png) to prevent duplicates
            $new_img_name = "user_" . $user_id . "." . $img_ext;
            $upload_path = 'uploads/users/' . $new_img_name;
            
            // Move file
            if (move_uploaded_file($img_tmp, $upload_path)) {
                $image_sql = ", profile_image = '$new_img_name'";
                // Update session immediately so header updates
                $_SESSION['profile_image'] = $new_img_name;
            } else {
                redirect('profile.php?error=upload_fail');
            }
        } else {
             redirect('profile.php?error=invalid_file');
        }
    }

    // 3. Update Database
    // Combine SQL parts dynamically
    $sql = "UPDATE users SET username = '$username' $password_sql $image_sql WHERE id = '$user_id'";

    if ($con->query($sql)) {
        // Update session name
        $_SESSION['user_name'] = $username;
        redirect('profile.php?status=success');
    } else {
        // Debugging
        die("Error: " . $con->error);
    }

} else {
    redirect('profile.php');
}
?>