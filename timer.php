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
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/timer.css">

<div class="main-page-content">
    <div class="timer-flex-container">
        
        <div class="timer-section-left">
            <div class="timer-circle-wrapper">
                <svg class="timer-circle" viewBox="0 0 100 100">
                    <circle class="timer-track" cx="50" cy="50" r="45"></circle>
                    <circle class="timer-progress" id="main-progress-circle" cx="50" cy="50" r="45"></circle>
                </svg>
    
                <div class="timer-content">
                    <span id="main-status-text">Focus</span>
                    <span id="main-timer-display">25:00</span>
                </div>
            </div>

            <div class="timer-actions">
                <button class="action-btn icon-btn" id="resetBtn" title="Reset">
                    <span class="material-icons-outlined">replay</span>
                </button>

                <button class="action-btn primary-btn" id="main-toggle-btn" title="Start/Pause">
                    <span class="material-icons-outlined" id="main-icon">play_arrow</span>
                </button>

                <button class="action-btn icon-btn" id="skipBtn" title="Skip to next mode">
                    <span class="material-icons-outlined">skip_next</span>
                </button>
            </div>

            <div class="mode-indicator">
                <span class="indicator-dot active-dot" id="dot-focus"></span>
                <span class="indicator-dot" id="dot-break"></span>
            </div>

            <div class="mode-labels">
                <span id="label-focus" class="active-label">Study Time</span>
                <span id="label-break">Short Break</span>
            </div>
        </div>

        <div class="settings-section-right">
            <div class="config-card">
                <h2>Timer Configuration</h2>  
                
                <div class="setting-group">
                    <label>Select Preset Mode</label>
                    <select id="presetSelector" class="timer-select">
                        <option value="25-5">Standard (25/5)</option>
                        <option value="50-10">Deep Work (50/10)</option>
                        <option value="15-3">Quick Sprint (15/3)</option>
                    </select>
                </div>
                <p class="hint-text">Hint: (Focus Time / Break Time)</p>

                <hr class="separator">
                
                <h3>Custom Adjustments</h3>
                
                <div class="setting-group slider-group">
                    <label>Focus Time</label>
                    <div class="slider-control">
                        <input type="range" id="customFocusSlider" min="5" max="90" step="5" value="25">
                        <span class="slider-value" id="focusTimeValue">25 min</span>
                    </div>
                </div>
                
                <div class="setting-group slider-group">
                    <label>Short Break</label>
                    <div class="slider-control">
                        <input type="range" id="customBreakSlider" min="1" max="30" step="1" value="5">
                        <span class="slider-value" id="breakTimeValue">05 min</span>
                    </div>
                </div>
                
                <button class="apply-custom-btn" id="applyCustomBtn">Apply Settings</button>
            </div>
        </div>

    </div>
</div>

<!--<audio id="timerEndSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3"></audio>-->

<script src="<?php echo URL_ROOT; ?>/assets/js/timer-universal.js"></script>

<?php include_once('layouts/footer.php');?>