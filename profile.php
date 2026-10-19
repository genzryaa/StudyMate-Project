<?php 
require_once 'includes/load.php';
require_user();

// Check login
if (!is_logged_in()) {
    redirect('auth/auth.php');
}

// 1. DATA FETCHING
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$result = $con->query($query); // Use $con, not $db
$user = $result->fetch_assoc();

$userName = $user['username']; 
$userEmail = $user['email'];

// Profile Image Logic (Checks if file exists in uploads folder)
// Note: We use 'uploads/users/' for user uploaded content
$db_image = $user['profile_image'];
$image_path = 'uploads/users/' . $db_image;

if (!empty($db_image) && file_exists($image_path)) {
    $profileImageSrc = $image_path;
} else {
    // Fallback to asset default
    $profileImageSrc = 'assets/img/default.png'; 
}

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">

<div class="main-page-content">
    
    <div class="profile-container" style="max-width: 800px; margin: 0 auto;">
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> Profile updated successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php if ($_GET['error'] == 'p_mismatch'): ?>
                    <i class="fa fa-exclamation-triangle"></i> Passwords do not match.
                <?php elseif ($_GET['error'] == 'upload_fail'): ?>
                    <i class="fa fa-times-circle"></i> Image upload failed.
                <?php else: ?>
                    <i class="fa fa-exclamation-circle"></i> An error occurred.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="profile-card" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            
            <div class="profile-header-info" style="text-align: center;">
                <div class="profile-avatar-wrapper" style="position: relative; width: 120px; height: 120px; margin: 0 auto 20px;">
                    <div class="avatar-container" style="width: 100%; height: 100%; border-radius: 50%; overflow: hidden; border: 4px solid var(--primary-blue);">
                        <img src="<?php echo URL_ROOT . '/' . $profileImageSrc; ?>?t=<?php echo time(); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>

                <div class="user-details">
                    <h2 style="margin-bottom: 5px; color: var(--primary-blue);"><?php echo htmlspecialchars($userName); ?></h2>
                    <p class="user-email-display" style="color: #777;"><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
                
                <button class="edit-profile-btn" id="edit-profile-toggle" type="button" style="margin-top: 20px; background: var(--primary-blue); color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <span class="material-icons-outlined" style="font-size: 18px;">edit</span> Edit Profile
                </button>
            </div>

            <div class="edit-form-wrapper" id="edit-form-wrapper" style="display: none; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <h3 style="margin-bottom: 20px;">Edit Details</h3>
                
                <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($userName); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="profile_pic" accept="image/*" style="border: 1px solid #ddd; padding: 5px;">
                        <small style="color: #888;">Allowed formats: JPG, PNG, GIF</small>
                    </div>

                    <div class="form-group" style="display: flex; gap: 15px;">
                        <div style="flex: 1;">
                            <label>New Password</label>
                            <input type="password" name="new_password" placeholder="Leave blank to keep current" minlength="8">
                        </div>
                        <div style="flex: 1;">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" minlength="8">
                        </div>
                    </div>
                    
                    <div class="form-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="cancel-edit-btn" class="cancel">Cancel</button>
                        <button type="submit" name="update_btn" class="save">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
    .form-group input[type="text"], 
    .form-group input[type="password"] { 
        width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; font-size: 14px; 
    }
    
    .save { background: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 500; }
    .save:hover { background: #218838; }
    
    .cancel { background: #f8f9fa; border: 1px solid #ddd; color: #333; padding: 12px 25px; border-radius: 8px; cursor: pointer; }
    .cancel:hover { background: #e2e6ea; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('edit-profile-toggle');
    const cancelBtn = document.getElementById('cancel-edit-btn');
    const formWrapper = document.getElementById('edit-form-wrapper');

    // Show Form
    if (editBtn && formWrapper) {
        editBtn.addEventListener('click', function() {
            formWrapper.style.display = 'block'; // Show the form
            editBtn.style.display = 'none';      // Hide the edit button
        });
    }

    // Hide Form (Cancel)
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            formWrapper.style.display = 'none';  // Hide the form
            editBtn.style.display = 'inline-flex'; // Show the edit button again
        });
    }
});
</script>
<?php include_once('layouts/footer.php'); ?>