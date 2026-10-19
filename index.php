<?php
require_once 'includes/load.php';
require_user();

if (!is_logged_in()) { redirect('auth/auth.php'); }

// 1. SET TIMEZONE (Crucial for "Today" logic)
// Change this if you are not in Malaysia/Singapore
date_default_timezone_set('Asia/Kuala_Lumpur'); 

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d'); // Current date: e.g., 2026-01-05

// --- 1. STREAK LOGIC ---
$u_sql = "SELECT streak, last_login FROM users WHERE id='$user_id'";
$u_res = $con->query($u_sql);
$u_data = $u_res->fetch_assoc();

$current_streak = isset($u_data['streak']) ? $u_data['streak'] : 0;
$last_login = $u_data['last_login'];

if ($last_login == $today) {
    // Already visited today
} elseif ($last_login == date('Y-m-d', strtotime('-1 day'))) {
    $current_streak++;
    $con->query("UPDATE users SET streak='$current_streak', last_login='$today' WHERE id='$user_id'");
} else {
    $current_streak = 1;
    $con->query("UPDATE users SET streak='$current_streak', last_login='$today' WHERE id='$user_id'");
}

// --- 2. STATS CALCULATION (ALL 4 TABLES) ---

// A. PENDING (Sum of active items)
$p_task = $con->query("SELECT COUNT(*) as total FROM tasks WHERE user_id='$user_id' AND status != 'completed'")->fetch_assoc()['total'];
$p_class = $con->query("SELECT COUNT(*) as total FROM classes WHERE user_id='$user_id' AND status != 'completed' AND DATE(date) >= '$today'")->fetch_assoc()['total'];
$p_exam = $con->query("SELECT COUNT(*) as total FROM exams WHERE user_id='$user_id' AND status != 'completed' AND DATE(date) >= '$today'")->fetch_assoc()['total'];
$p_extra = $con->query("SELECT COUNT(*) as total FROM extra_activities WHERE user_id='$user_id' AND status != 'completed' AND DATE(date) >= '$today'")->fetch_assoc()['total'];

$pending_total = $p_task + $p_class + $p_exam + $p_extra;

// B. OVERDUE (Tasks only)
$overdue_total = $con->query("SELECT COUNT(*) as total FROM tasks WHERE user_id='$user_id' AND due_date < '$today' AND status != 'completed'")->fetch_assoc()['total'];

// C. COMPLETED (Sum of ALL completed items from 4 tables) - [UPDATED]
$c_task = $con->query("SELECT COUNT(*) as total FROM tasks WHERE user_id='$user_id' AND status = 'completed'")->fetch_assoc()['total'];
$c_class = $con->query("SELECT COUNT(*) as total FROM classes WHERE user_id='$user_id' AND status = 'completed'")->fetch_assoc()['total'];
$c_exam = $con->query("SELECT COUNT(*) as total FROM exams WHERE user_id='$user_id' AND status = 'completed'")->fetch_assoc()['total'];
$c_extra = $con->query("SELECT COUNT(*) as total FROM extra_activities WHERE user_id='$user_id' AND status = 'completed'")->fetch_assoc()['total'];

// Calculate Grand Total
$completed_total = $c_task + $c_class + $c_exam + $c_extra;

// --- 3. FETCH TODAY'S AGENDA (FROM ALL 4 TABLES) ---
$todays_agenda = [];

// A. Classes
// Use DATE(date) = '$today' to match robustly
$res = $con->query("SELECT subject as title, start_time as time, 'class' as type FROM classes WHERE user_id='$user_id' AND DATE(date) = '$today'");
if($res) { while($row = $res->fetch_assoc()) $todays_agenda[] = $row; }

// B. Tasks
$res = $con->query("SELECT title, due_time as time, 'task' as type FROM tasks WHERE user_id='$user_id' AND due_date = '$today' AND status != 'completed'");
if($res) { while($row = $res->fetch_assoc()) $todays_agenda[] = $row; }

// C. Exams
$res = $con->query("SELECT exam_name as title, '09:00:00' as time, 'exam' as type FROM exams WHERE user_id='$user_id' AND DATE(date) = '$today'");
if($res) { while($row = $res->fetch_assoc()) $todays_agenda[] = $row; }

// D. Extra Activities
// Use the default time '10:00:00' if start_time column is missing, or change back if you fixed it
$res = $con->query("SELECT title, '10:00:00' as time, 'activity' as type FROM extra_activities WHERE user_id='$user_id' AND DATE(date) = '$today'");
if($res) { while($row = $res->fetch_assoc()) $todays_agenda[] = $row; }

// Sort by time
usort($todays_agenda, function($a, $b) {
    return strtotime($a['time']) - strtotime($b['time']);
});

// Limit to top 3 for display
$todays_agenda_slice = array_slice($todays_agenda, 0, 3); 

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
<style>
    @media (max-width: 992px) { .nav-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
        .hero-row { flex-direction: column; }
        .nav-grid { grid-template-columns: 1fr; }
        .pomodoro-card, .calendar-card { min-height: auto; width: 100%; }
        .nc-select { padding: 10px; font-size: 16px; }
    }
</style>

<div class="main-page-content">
    
    <div class="hero-row">
        
        <?php 
        $hour = date('H');
        if ($hour < 12) $greeting = "Good Morning!";
        elseif ($hour < 18) $greeting = "Good Afternoon!";
        else $greeting = "Good Evening!";
        ?>
        <div class="hero-card pomodoro-card">
            <div class="pomo-info">
                <h4 class="pomo-greeting"><?php echo $greeting; ?></h4>
                <p class="pomo-status-text" id="mini-status-text">Ready to focus?</p>
                
                <div class="pomo-controls">
                    <button id="mini-reset-btn" class="btn-pomo-settings">
                        <span class="material-icons-outlined">replay</span>
                    </button>
                    <button id="mini-toggle-btn" class="btn-pomo-action">
                        <span class="material-icons-outlined" id="mini-icon">play_arrow</span>
                    </button>
                    <a href="timer.php" class="btn-pomo-settings">
                        <span class="material-icons-outlined">settings</span>
                    </a>
                </div>
            </div>
            <div class="pomo-progress">
                <svg class="progress-ring" width="100" height="100">
                    <circle class="progress-ring__circle-bg" stroke="rgba(255,255,255,0.3)" stroke-width="6" fill="transparent" r="42" cx="50" cy="50"/>
                    <circle class="progress-ring__circle" id="mini-progress-circle" stroke="white" stroke-width="6" fill="transparent" r="42" cx="50" cy="50"/>
                </svg>
                <div class="pomo-timer-text" id="mini-timer-display">25:00</div>
            </div>
        </div>

        <a href="calendar.php" class="hero-card calendar-card">
            <div class="cal-left">
                <span class="cal-big-day"><?php echo date('d'); ?></span>
                <span class="cal-month"><?php echo date('F'); ?></span>
            </div>

            <div class="cal-middle-list">
    <?php if(empty($todays_agenda_slice)): ?>
        
        <div class="empty-cal-widget" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #9ca3af; font-size: 14px;">
            <span class="material-icons-outlined" style="font-size: 28px; margin-bottom: 8px; color: #d1d5db;">event_available</span>
            <span>Free day! No events.</span>
        </div>

    <?php else: ?>
        
        <?php foreach($todays_agenda_slice as $item): ?>
            <div class="cal-widget-item" style="display: flex; align-items: center; margin-bottom: 12px;">
                
                <div class="cw-line" style="
                    width: 5px; 
                    height: 28px; 
                    margin-right: 12px; 
                    border-radius: 4px; 
                    flex-shrink: 0;
                    background-color: <?php 
                        if($item['type'] == 'exam') echo '#ef4444';      // Red
                        elseif($item['type'] == 'class') echo '#10b981'; // Green
                        elseif($item['type'] == 'task') echo '#3b82f6';  // Blue
                        else echo '#f59e0b';                             // Orange
                    ?>;">
                </div>
                
                <div class="cw-title" style="
                    white-space: nowrap; 
                    overflow: hidden; 
                    text-overflow: ellipsis; 
                    max-width: 160px; 
                    font-size: 15px; /* Increased Size */
                    font-weight: 500; 
                    color: #1f2937;">
                    <?php echo htmlspecialchars($item['title']); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if(count($todays_agenda) > 3): ?>
            <div style="font-size: 12px; color: #9ca3af; text-align: center; margin-top: 5px; font-weight: 500;">
                +<?php echo count($todays_agenda) - 3; ?> more events
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

            <div class="cal-right">
                <span class="cal-weekday"><?php echo date('l'); ?></span>
                <span class="cal-link">
                    View Full 
                    <span class="material-icons-outlined" style="font-size:16px">arrow_forward</span>
                </span>
            </div>
        </a>
    </div>

    <div class="stats-bar">
        <div class="stat-strip">
            <span class="st-label">Pending</span>
            <span class="st-val blue"><?php echo $pending_total; ?></span>
        </div>
        <div class="stat-strip">
            <span class="st-label">Overdue</span>
            <span class="st-val red"><?php echo $overdue_total; ?></span>
        </div>
        <div class="stat-strip">
            <span class="st-label">Completed</span>
            <span class="st-val green"><?php echo $completed_total; ?></span>
        </div>
        <div class="stat-strip">
            <span class="st-label">Streak 🔥</span>
            <span class="st-val orange"><?php echo $current_streak; ?> Days</span>
        </div>
    </div>

    <div class="nav-grid">
            
            <?php 
            $task_data = ['Current' => [], 'Overdue' => [], 'Past' => []];
            $t_res = $con->query("SELECT * FROM tasks WHERE user_id='$user_id' ORDER BY due_date ASC");
            while($row = $t_res->fetch_assoc()) {
                $is_overdue = ($row['due_date'] < $today);
                if ($row['status'] == 'completed') {
                    $task_data['Past'][] = $row['title'];
                } elseif ($is_overdue) {
                    $task_data['Overdue'][] = $row['title'];
                } else {
                    $task_data['Current'][] = $row['title'];
                }
            }
            ?>
            <div class="nav-card widget-mode">
                <div class="nc-header">
                    <div class="nav-icon bg-blue"><span class="material-icons-outlined">task_alt</span></div>
                    <h4>Tasks</h4>
                </div>
                <select class="nc-select" onchange="filterWidget(this, 'task-list')">
                    <option value="" disabled selected>Select Category</option>
                    <option value="Current">Current</option>
                    <option value="Overdue">Overdue</option>
                    <option value="Past">Past</option>
                </select>
                <div class="nc-results" id="task-list">
                    <div class="nc-empty-msg" style="display:none; padding:5px; color:#999; font-size:12px;">No items found.</div>
                    <?php foreach($task_data as $cat => $titles): ?>
                        <?php foreach($titles as $t): ?>
                            <div class="nc-item" data-category="<?php echo $cat; ?>">
                                • <?php echo htmlspecialchars($t); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php 
            $class_data = ['Current' => [], 'Past' => []];
            $c_res = $con->query("SELECT subject, date FROM classes WHERE user_id='$user_id' ORDER BY date ASC");
            while($row = $c_res->fetch_assoc()) {
                if ($row['date'] < $today) {
                    $class_data['Past'][] = $row['subject'];
                } else {
                    $class_data['Current'][] = $row['subject'];
                }
            }
            ?>
            <div class="nav-card widget-mode">
                <div class="nc-header">
                    <div class="nav-icon bg-green"><span class="material-icons-outlined">class</span></div>
                    <h4>Classes</h4>
                </div>
                <select class="nc-select" onchange="filterWidget(this, 'class-list')">
                    <option value="" disabled selected>Select Category</option>
                    <option value="Current">Current</option>
                    <option value="Past">Past</option>
                </select>
                <div class="nc-results" id="class-list">
                    <div class="nc-empty-msg" style="display:none; padding:5px; color:#999; font-size:12px;">No items found.</div>
                    <?php foreach($class_data as $cat => $subs): ?>
                        <?php foreach($subs as $s): ?>
                            <div class="nc-item" data-category="<?php echo $cat; ?>">
                                • <?php echo htmlspecialchars($s); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php 
            $exam_data = ['Current' => [], 'Past' => []];
            $e_res = $con->query("SELECT exam_name, date FROM exams WHERE user_id='$user_id' ORDER BY date ASC");
            while($row = $e_res->fetch_assoc()) {
                if ($row['date'] < $today) {
                    $exam_data['Past'][] = $row['exam_name'];
                } else {
                    $exam_data['Current'][] = $row['exam_name'];
                }
            }
            ?>
            <div class="nav-card widget-mode">
                <div class="nc-header">
                    <div class="nav-icon bg-red"><span class="material-icons-outlined">description</span></div>
                    <h4>Exams</h4>
                </div>
                <select class="nc-select" onchange="filterWidget(this, 'exam-list')">
                    <option value="" disabled selected>Select Category</option>
                    <option value="Current">Current</option>
                    <option value="Past">Past</option>
                </select>
                <div class="nc-results" id="exam-list">
                    <div class="nc-empty-msg" style="display:none; padding:5px; color:#999; font-size:12px;">No items found.</div>
                    <?php foreach($exam_data as $cat => $names): ?>
                        <?php foreach($names as $n): ?>
                            <div class="nc-item" data-category="<?php echo $cat; ?>">
                                • <?php echo htmlspecialchars($n); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php 
            $extra_data = ['Academic' => [], 'Non-Academic' => []];
            // Ensure your 'extra_activities' table has a 'category' column
            $ex_res = $con->query("SELECT title, category FROM extra_activities WHERE user_id='$user_id'");
            while($row = $ex_res->fetch_assoc()) {
                $db_cat = ucwords(strtolower($row['category'])); 
                if ($db_cat == 'Academic') {
                    $extra_data['Academic'][] = $row['title'];
                } else {
                    $extra_data['Non-Academic'][] = $row['title'];
                }
            }
            ?>
            <div class="nav-card widget-mode">
                <div class="nc-header">
                    <div class="nav-icon bg-orange"><span class="material-icons-outlined">star</span></div>
                    <h4>Extra</h4>
                </div>
                <select class="nc-select" onchange="filterWidget(this, 'extra-list')">
                    <option value="" disabled selected>Select Category</option>
                    <option value="Academic">Academic</option>
                    <option value="Non-Academic">Non-Academic</option>
                </select>
                <div class="nc-results" id="extra-list">
                    <div class="nc-empty-msg" style="display:none; padding:5px; color:#999; font-size:12px;">No items found.</div>
                    <?php foreach($extra_data as $cat => $titles): ?>
                        <?php foreach($titles as $t): ?>
                            <div class="nc-item" data-category="<?php echo $cat; ?>">
                                • <?php echo htmlspecialchars($t); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>

    </div> <script>
    function filterWidget(selectEl, listId) {
        const category = selectEl.value;
        const listContainer = document.getElementById(listId);
        const items = listContainer.querySelectorAll('.nc-item');
        const emptyMsg = listContainer.querySelector('.nc-empty-msg');
        
        listContainer.style.display = 'block';
        
        let found = false;
        items.forEach(item => {
            if (item.dataset.category === category) {
                item.style.display = 'block';
                found = true;
            } else {
                item.style.display = 'none';
            }
        });

        if(emptyMsg) {
            emptyMsg.style.display = found ? 'none' : 'block';
        }
    }
    </script>

</div>

<script src="<?php echo URL_ROOT; ?>/assets/js/timer-universal.js"></script>

<?php include_once('layouts/footer.php'); ?>