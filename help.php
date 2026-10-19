<?php
require_once 'includes/load.php';
require_user();

// Check login
if (!is_logged_in()) {
    redirect('auth/auth.php');
}

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/help.css">

<div class="main-page-content">
    
    <div class="help-container">
        
        <div class="help-hero">
            <h1>How can we help you?</h1>
            <div class="search-box">
                <span class="material-icons-outlined">search</span>
                <input type="text" id="faqSearch" placeholder="Search for answers (e.g. 'timer', 'password')..." onkeyup="filterFAQs()">
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'ticket_sent'): ?>
            <div class="success-banner">
                <span class="material-icons-outlined">check_circle</span>
                Your message has been sent! We will get back to you shortly.
            </div>
        <?php endif; ?>

        <div class="help-grid">
            
            <div class="faq-section">
                <h2>Frequently Asked Questions</h2>
                
                <div class="accordion-wrapper" id="faqList">
                    
                    <div class="accordion-item">
                        <button class="accordion-header">
                            <span>How do I reset my password?</span>
                            <span class="material-icons-outlined arrow">expand_more</span>
                        </button>
                        <div class="accordion-content">
                            <p>You can reset your password by going to <strong>Settings</strong> > <strong>Security</strong>, or by clicking "Edit Profile" on your profile page.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header">
                            <span>How does the Focus Timer work?</span>
                            <span class="material-icons-outlined arrow">expand_more</span>
                        </button>
                        <div class="accordion-content">
                            <p>The timer uses the Pomodoro technique. By default, it sets a 25-minute focus session followed by a 5-minute break. You can customize these times in the timer settings.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header">
                            <span>Can I add my exam schedule?</span>
                            <span class="material-icons-outlined arrow">expand_more</span>
                        </button>
                        <div class="accordion-content">
                            <p>Yes! Go to the <strong>Activities</strong> page, click "Add New", and select "Exam" from the dropdown menu.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header">
                            <span>Is there a dark mode?</span>
                            <span class="material-icons-outlined arrow">expand_more</span>
                        </button>
                        <div class="accordion-content">
                            <p>Yes, you can toggle Dark Mode at the bottom of the sidebar or inside the Settings page.</p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="contact-section">
                <div class="contact-card">
                    <h3>Still need help?</h3>
                    <p>Send us a message and we'll help you out.</p>
                    
                    <form action="submit_ticket.php" method="POST">
                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject" class="help-input" required>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Bug Report">Report a Bug</option>
                                <option value="Account Issue">Account Issue</option>
                                <option value="Feature Request">Feature Request</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" rows="5" class="help-input" placeholder="Describe your issue..." required></textarea>
                        </div>

                        <button type="submit" name="send_ticket" class="send-btn">
                            Send Message <span class="material-icons-outlined">send</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="<?php echo URL_ROOT; ?>/assets/js/help.js"></script>

<?php include_once('layouts/footer.php'); ?>