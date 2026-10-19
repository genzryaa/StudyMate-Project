<?php
require_once 'includes/load.php';
require_user();

// Check Login
if (!is_logged_in()) {
    redirect('auth/auth.php');
}

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">

<div class="main-page-content">
    <div class="settings-wrapper">
        
        <div class="page-title">
            <h1>Account Settings</h1>
            <p>Manage your preferences in one place.</p>
        </div>

        <div class="settings-grid-layout">
            
            <div class="left-column">
                
                <div class="settings-card">
                    <div class="card-header">
                        <div class="icon-box purple">
                            <span class="material-icons-outlined">tune</span>
                        </div>
                        <h2>App Preferences</h2>
                    </div>
                    
                    <div class="card-body">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="title-with-icon">
                                    <span class="material-icons-outlined">dark_mode</span>
                                    <strong>Dark Mode</strong>
                                </div>
                                <p>Switch between light and dark themes.</p>
                            </div>
                            
                            <label class="switch">
                                <input type="checkbox" id="darkModeToggle" onclick="toggleDarkMode(event)">
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <hr class="divider">

                        <div class="settings-row clickable-row" onclick="location.href='profile.php'">
                            <div class="setting-info">
                                <div class="title-with-icon">
                                    <span class="material-icons-outlined">security</span>
                                    <strong>Security</strong>
                                </div>
                                <p>Update your password in <span class="inline-link">Profile Settings</span>.</p>
                            </div>
                            <span class="material-icons-outlined arrow-icon">chevron_right</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="right-column">
                
                <div class="settings-card help-card">
                    <div class="card-header">
                        <div class="icon-box green">
                            <span class="material-icons-outlined">support_agent</span>
                        </div>
                        <h2>Help & Support</h2>
                    </div>
                    
                    <div class="card-body">
                        <p class="support-text">Need help using StudyMate? Check our guides or contact support.</p>
                        
                        <ul class="support-list">
                            <li>
                                <span class="material-icons-outlined">help_outline</span>
                                <span>FAQ & Guides</span>
                            </li>
                            <li>
                                <span class="material-icons-outlined">bug_report</span>
                                <span>Report a Bug</span>
                            </li>
                            <li>
                                <span class="material-icons-outlined">email</span>
                                <span>Contact Admin</span>
                            </li>
                        </ul>

                        <a href="<?php echo URL_ROOT; ?>/help.php" class="btn-outline full-width">
                            Visit Help Center
                        </a>
                    </div>
                </div>

            </div>

        </div> 
        <div class="bottom-section">
            <div class="settings-card danger-zone">
                <div class="side-by-side-btns">
                    <a href="<?php echo URL_ROOT; ?>/auth/logout.php" class="btn-secondary action-btn">
                        <span class="material-icons-outlined">logout</span> Logout
                    </a>

                    <button onclick="confirmDelete()" class="btn-danger action-btn">
                        <span class="material-icons-outlined">delete_forever</span> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. DELETE ACCOUNT CONFIRMATION
    function confirmDelete() {
        if(confirm("Are you sure you want to PERMANENTLY delete your account? This action cannot be undone.")) {
            window.location.href = "delete_account.php";
        }
    }
    
    // 2. DARK MODE ALERT (Placeholder)
    window.toggleDarkMode = function(event) {
        // This stops the toggle from physically moving/checking
        event.preventDefault(); 
        
        // Shows the message
        alert("Dark mode coming soon!");
    };
</script>

<?php include_once('layouts/footer.php'); ?>