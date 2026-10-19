<?php
require_once 'includes/load.php';
require_user();
if (!is_logged_in()) { redirect('auth/auth.php'); }

// --- 1. SETTINGS & INPUTS ---
$view = isset($_GET['view']) ? $_GET['view'] : 'day'; 
$date_input = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$today_date = date('Y-m-d');
$timestamp = strtotime($date_input);

// --- 2. CALCULATE LABELS & LINKS ---
if ($view == 'day') {
    $label = date('l, F j, Y', $timestamp);
    $prev = "?view=day&date=" . date('Y-m-d', strtotime('-1 day', $timestamp));
    $next = "?view=day&date=" . date('Y-m-d', strtotime('+1 day', $timestamp));
} elseif ($view == 'week') {
    $day_of_week = date('N', $timestamp);
    $start_ts = strtotime('-' . ($day_of_week - 1) . ' days', $timestamp);
    $end_ts   = strtotime('+6 days', $start_ts);
    $label = date('M j', $start_ts) . " - " . date('M j, Y', $end_ts);
    
    $start_date = date('Y-m-d', $start_ts);
    $end_date   = date('Y-m-d', $end_ts);
    
    $prev = "?view=week&date=" . date('Y-m-d', strtotime('-1 week', $timestamp));
    $next = "?view=week&date=" . date('Y-m-d', strtotime('+1 week', $timestamp));
} else { // Month
    $label = date('F Y', $timestamp);
    $start_date = date('Y-m-01', $timestamp); 
    $end_date   = date('Y-m-t', $timestamp);
    
    $prev = "?view=month&date=" . date('Y-m-d', strtotime('-1 month', $timestamp));
    $next = "?view=month&date=" . date('Y-m-d', strtotime('+1 month', $timestamp));
}

// --- 3. FETCH EVENTS ---
$user_id = $_SESSION['user_id'];
$events = [];

function fetch_events($con, $table, $type, $user_id, $start, $end) {
    global $events;
    if($table == 'tasks') {
        $sql = "SELECT title, due_date as date, due_time as time, 'task' as type FROM tasks WHERE user_id='$user_id' AND due_date BETWEEN '$start' AND '$end'";
    } elseif($table == 'classes') {
        $sql = "SELECT subject as title, class_name, room, date, start_time as time, 'class' as type FROM classes WHERE user_id='$user_id' AND date BETWEEN '$start' AND '$end'";
    } elseif($table == 'exams') {
        $sql = "SELECT exam_name as title, place, date, '09:00:00' as time, 'exam' as type FROM exams WHERE user_id='$user_id' AND date BETWEEN '$start' AND '$end'";
    } elseif($table == 'extra_activities') {
        $sql = "SELECT title, date, time, 'extra' as type FROM extra_activities WHERE user_id='$user_id' AND date BETWEEN '$start' AND '$end'";
    }
    
    $res = $con->query($sql);
    if($res) { 
        while($row = $res->fetch_assoc()) { 
            $events[$row['date']][] = $row; 
        } 
    }
}

// Default dates if not set
if(!isset($start_date)) { $start_date = $date_input; $end_date = $date_input; }

fetch_events($con, 'tasks', 'task', $user_id, $start_date, $end_date);
fetch_events($con, 'exams', 'exam', $user_id, $start_date, $end_date);
fetch_events($con, 'classes', 'class', $user_id, $start_date, $end_date);
fetch_events($con, 'extra_activities', 'extra', $user_id, $start_date, $end_date);

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/calendar.css">

<div class="main-page-content">
    <div class="activities-container">
        
        <div class="calendar-header">
            <div class="cal-nav-group">
                <a href="<?php echo $prev; ?>" class="nav-btn"><span class="material-icons-outlined">chevron_left</span></a>
                <h2 class="cal-title"><?php echo $label; ?></h2>
                <a href="<?php echo $next; ?>" class="nav-btn"><span class="material-icons-outlined">chevron_right</span></a>
            </div>

            <div class="cal-view-group">
                <a href="?view=<?php echo $view; ?>&date=<?php echo $today_date; ?>" class="btn-today">Today</a>
                <div class="divider-vertical"></div>
                <a href="?view=day&date=<?php echo $date_input; ?>" class="view-btn <?php echo ($view=='day')?'active':''; ?>">Day</a>
                <a href="?view=week&date=<?php echo $date_input; ?>" class="view-btn <?php echo ($view=='week')?'active':''; ?>">Week</a>
                <a href="?view=month&date=<?php echo $date_input; ?>" class="view-btn <?php echo ($view=='month')?'active':''; ?>">Month</a>
            </div>
        </div>

        <?php if($view == 'day'): ?>
            <div class="day-view-container">
                <div class="time-list-wrapper">
                    <?php 
                    // Organize events
                    $events_by_hour = [];
                    if(isset($events[$date_input])) {
                        foreach($events[$date_input] as $evt) {
                            $h = (int)date('G', strtotime($evt['time']));
                            $events_by_hour[$h][] = $evt;
                        }
                    }

                    // Loop 0 to 23
                    for($hour = 0; $hour < 24; $hour++): 
                        $am_pm = ($hour < 12) ? 'AM' : 'PM';
                        $display_hour = ($hour == 0 || $hour == 12) ? 12 : $hour % 12;
                        $time_label = sprintf("%02d:00 %s", $display_hour, $am_pm);
                        $has_event = isset($events_by_hour[$hour]);
                    ?>
                        <div class="schedule-row">
                            <div class="schedule-time">
                                <span><?php echo $time_label; ?></span>
                                <div class="time-dot"></div>
                            </div>

                            <div class="schedule-slot" id="slot-<?php echo $hour; ?>">
                                
                                <?php if($has_event): ?>
                                    <?php foreach($events_by_hour[$hour] as $evt): 
                                        $detail = "";
                                        if($evt['type'] == 'class') $detail = $evt['class_name'] . " (" . $evt['room'] . ")";
                                        if($evt['type'] == 'exam') $detail = $evt['place'];
                                    ?>
                                        <div class="schedule-card <?php echo $evt['type']; ?>">
                                            <div class="sc-content">
                                                <h3><?php echo htmlspecialchars($evt['title']); ?></h3>
                                                <?php if($detail): ?><p><?php echo htmlspecialchars($detail); ?></p><?php endif; ?>
                                            </div>
                                            <span class="type-tag"><?php echo ucfirst($evt['type']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-slot"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

        <?php elseif($view == 'week'): ?>
            <div class="week-view-container">
                
                <div class="week-header-row">
                    <div class="time-col-header"></div>
                    <?php 
                    $curr = strtotime($start_date); 
                    for($i=0; $i<7; $i++): 
                        $this_date = date('Y-m-d', $curr);
                        $day_name  = date('D', $curr);
                        $day_num   = date('d', $curr);
                        $is_today  = ($this_date == $today_date) ? 'today' : '';
                    ?>
                        <div class="day-col-header <?php echo $is_today; ?>">
                            <span class="wk-day"><?php echo $day_name; ?></span>
                            <span class="wk-num"><?php echo $day_num; ?></span>
                        </div>
                    <?php $curr = strtotime('+1 day', $curr); endfor; ?>
                </div>

                <div class="week-body-scroll">
                    <?php 
                    // 1. Prepare Grid Data
                    $week_grid = [];
                    // Initialize empty
                    $temp_date = strtotime($start_date);
                    for($d=0; $d<7; $d++) {
                        $d_str = date('Y-m-d', $temp_date);
                        for($h=0; $h<24; $h++) $week_grid[$d_str][$h] = [];
                        $temp_date = strtotime('+1 day', $temp_date);
                    }
                    // Fill with events
                    $chk_start = strtotime($start_date);
                    $chk_end   = strtotime($end_date);
                    for($t = $chk_start; $t <= $chk_end; $t = strtotime('+1 day', $t)) {
                        $d_str = date('Y-m-d', $t);
                        if(isset($events[$d_str])) {
                            foreach($events[$d_str] as $evt) {
                                $h = (int)date('G', strtotime($evt['time']));
                                $week_grid[$d_str][$h][] = $evt;
                            }
                        }
                    }

                    // 2. Render Rows (0-23)
                    for($hour = 0; $hour < 24; $hour++): 
                        $am_pm = ($hour < 12) ? 'AM' : 'PM';
                        $display_hour = ($hour == 0 || $hour == 12) ? 12 : $hour % 12;
                        $time_label = sprintf("%02d:00 %s", $display_hour, $am_pm);
                    ?>
                        <div class="week-hour-row">
                            <div class="week-time-label">
                                <span><?php echo $time_label; ?></span>
                            </div>

                            <?php 
                            $col_date = strtotime($start_date);
                            for($d=0; $d<7; $d++): 
                                $date_key = date('Y-m-d', $col_date);
                                $cell_events = $week_grid[$date_key][$hour];
                                
                                // CHECK IF THIS CELL IS 'TODAY'
                                $is_today_cell = ($date_key == $today_date);
                                $cell_class = $is_today_cell ? 'today-cell' : '';
                                
                                // IF TODAY, ADD UNIQUE ID FOR JS TARGET
                                $cell_id = $is_today_cell ? 'id="week-today-slot-' . $hour . '"' : '';
                            ?>
                                <div class="week-slot-cell <?php echo $cell_class; ?>" <?php echo $cell_id; ?>>
                                    <?php if(!empty($cell_events)): ?>
                                        <?php foreach($cell_events as $evt): ?>
                                            <div class="wk-task-chip <?php echo $evt['type']; ?>" title="<?php echo htmlspecialchars($evt['title']); ?>">
                                                <?php echo substr($evt['title'], 0, 10); ?>..
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php 
                                $col_date = strtotime('+1 day', $col_date); 
                            endfor; 
                            ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="calendar-grid">
                <div class="weekday">Mon</div><div class="weekday">Tue</div><div class="weekday">Wed</div>
                <div class="weekday">Thu</div><div class="weekday">Fri</div><div class="weekday">Sat</div><div class="weekday">Sun</div>
                <?php 
                $first_day_ts = strtotime($start_date);
                $start_offset = date('N', $first_day_ts) - 1; 
                $days_in_m = date('t', $first_day_ts);
                
                for($i=0; $i<$start_offset; $i++) echo "<div class='day-cell empty'></div>";

                for($d=1; $d<=$days_in_m; $d++):
                    $cur = date('Y-m-', $first_day_ts) . sprintf("%02d", $d);
                    $today_cls = ($cur == $today_date) ? 'today' : '';
                ?>
                    <div class="day-cell <?php echo $today_cls; ?>">
                        <span class="day-number"><?php echo $d; ?></span>
                        <div class="event-dots">
                        <?php if(isset($events[$cur])): foreach($events[$cur] as $evt): ?>
                            <div class="evt-dot <?php echo $evt['type']; ?>" title="<?php echo $evt['title']; ?>">
                                • <?php echo substr($evt['title'], 0, 8); ?>
                            </div>
                        <?php endforeach; endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php if($view == 'day' && $date_input == date('Y-m-d')): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    updateTimelinePosition();
    setInterval(updateTimelinePosition, 30000); // Update every 30s

    function updateTimelinePosition() {
        const now = new Date();
        const currentHour = now.getHours();
        const currentMin  = now.getMinutes();

        // Find the slot for the current hour
        const currentSlot = document.getElementById(`slot-${currentHour}`);

        if (!currentSlot) return;

        // Check if line exists, if not create it
        let line = document.querySelector('.current-time-line');
        if (!line) {
            line = document.createElement('div');
            line.className = 'current-time-line';
            line.innerHTML = '<div class="time-badge"></div>';
            currentSlot.appendChild(line);
        } else {
            // Move to correct slot if hour changed
            if (line.parentNode !== currentSlot) {
                currentSlot.appendChild(line);
            }
        }

        // Move the line to percentage
        const percent = (currentMin / 60) * 100;
        line.style.top = percent + "%";

        // Update Text
        const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const badge = line.querySelector('.time-badge');
        if (badge) badge.innerText = timeString;
    }
});
</script>
<?php endif; ?>

<?php include_once('layouts/footer.php'); ?>