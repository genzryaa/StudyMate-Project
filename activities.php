<?php
require_once 'includes/load.php';
require_user();
if (!is_logged_in()) { redirect('auth/auth.php'); }

$user_id = $_SESSION['user_id'];
$view = isset($_GET['view']) ? $_GET['view'] : 'tasks'; // Default to tasks

// Initialize arrays for sorting
$list_1 = []; // Tasks: Current | Exams/Classes: Current | Extra: Academic
$list_2 = []; // Tasks: Past    | Exams/Classes: Past    | Extra: Non-Academic
$list_3 = []; // Tasks: Overdue

$today = date('Y-m-d');

// --- 1. FETCH AND SORT DATA BASED ON VIEW ---
if ($view == 'tasks') {
    $sql = "SELECT * FROM tasks WHERE user_id = '$user_id' ORDER BY due_date ASC";
    $res = $con->query($sql);
    while ($row = $res->fetch_assoc()) {
        if ($row['status'] == 'completed') {
            $list_2[] = $row;
        } elseif ($row['due_date'] < $today) {
            $list_3[] = $row;
        } else {
            $list_1[] = $row;
        }
    }
} elseif ($view == 'classes') {
    $sql = "SELECT * FROM classes WHERE user_id = '$user_id' ORDER BY date ASC";
    $res = $con->query($sql);
    while ($row = $res->fetch_assoc()) {
        if ($row['date'] < $today || $row['status'] == 'completed') {
            $list_2[] = $row;
        } else {
            $list_1[] = $row;
        }
    }
} elseif ($view == 'exams') {
    $sql = "SELECT * FROM exams WHERE user_id = '$user_id' ORDER BY date ASC";
    $res = $con->query($sql);
    while ($row = $res->fetch_assoc()) {
        if ($row['date'] < $today || $row['status'] == 'completed') {
            $list_2[] = $row;
        } else {
            $list_1[] = $row;
        }
    }
} elseif ($view == 'extra') {
    $sql = "SELECT * FROM extra_activities WHERE user_id = '$user_id' ORDER BY date ASC";
    $res = $con->query($sql);
    while ($row = $res->fetch_assoc()) {
        if ($row['category'] == 'Academic') {
            $list_1[] = $row;
        } else {
            $list_2[] = $row;
        }
    }
}

include_once('layouts/header.php');
include_once('layouts/sidebar.php');
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">

<style>
    /* Container for the cards */
    .category-view { display: flex; flex-direction: column; gap: 15px; width: 100%; }

    /* The Activity Card Styling */
        .activity-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 5px solid #0D3B66; /* Decorative left border */
        
        /* --- WIDTH CONTROL --- */
        width: 310%;       /* 1. Make it span full width of parent */
        max-width: 310%;   /* 2. Or restrict it (e.g., 800px) */
        box-sizing: border-box; /* Ensures padding doesn't break width */
    }

    /* Card Content Typography */
    .card-info h3 { margin: 0 0 5px 0; font-size: 16px; color: #333; }
    .card-info p { margin: 0 0 8px 0; font-size: 13px; color: #666; }
    .date-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: #888; background: #f4f4f4; padding: 4px 8px; border-radius: 4px; }

    /* Action Buttons Area */
    .card-actions { display: flex; gap: 10px; }
    .btn-icon { color: #999; transition: color 0.2s; text-decoration: none; cursor: pointer; }
    .btn-icon{ color: #0D3B66; }
    .btn-icon.delete { color: #dc3545; }
    .btn-icon.complete{ color: #28a745; }
    .btn-icon:hover { color: #0D3B66; }
    .btn-icon.delete:hover { color: #dc3545; }
    .btn-icon.complete:hover { color: #28a745; }

    /* Empty State Styling */
    .empty-state { text-align: center; padding: 40px; color: #aaa; background: #fafafa; border-radius: 10px; border: 2px dashed #eee; }
</style>

<div class="main-page-content">
    <div class="activities-container">

        <div class="activities-header">
            <div class="header-left">
                <h1 class="page-title"><?php echo ucfirst($view); ?></h1>
                
                <div class="sub-tabs">
                    <?php if($view == 'tasks'): ?>
                        <button class="sub-tab active" onclick="showCategory('cat1')">Current</button>
                        <button class="sub-tab" onclick="showCategory('cat2')">Past</button>
                        <button class="sub-tab" onclick="showCategory('cat3')">Overdue</button>
                    <?php elseif($view == 'extra'): ?>
                        <button class="sub-tab active" onclick="showCategory('cat1')">Academic</button>
                        <button class="sub-tab" onclick="showCategory('cat2')">Non-Academic</button>
                    <?php else: // Exams & Classes ?>
                        <button class="sub-tab active" onclick="showCategory('cat1')">Current</button>
                        <button class="sub-tab" onclick="showCategory('cat2')">Past</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="header-right">
                <button class="btn-add-new" onclick="openAddModal()">
                    <span class="material-icons-outlined">add</span> Add New
                </button>
            </div>
        </div>

        <div class="activities-content">
            <div id="cat1" class="category-view">
                <?php render_activity_list($list_1, $view, 'cat1'); ?>
            </div>
            <div id="cat2" class="category-view" style="display:none;">
                <?php render_activity_list($list_2, $view, 'cat2'); ?>
            </div>
            <?php if($view == 'tasks'): ?>
            <div id="cat3" class="category-view" style="display:none;">
                <?php render_activity_list($list_3, $view, 'cat3'); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>
<div id="activityModal" class="custom-modal">
    <div class="modal-header">
        <h2 id="modalTitle">Add New <?php echo ucfirst($view); ?></h2>
        <span class="close-icon" onclick="closeModal()">&times;</span>
    </div>
    <form action="includes/add_activity.php" method="POST" id="activityForm">
        <input type="hidden" name="activity_type" value="<?php echo $view; ?>">
        
        <input type="hidden" name="id" id="edit_id" value="">

        <div class="modal-body">
            <?php include 'includes/activity_forms.php'; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
            <button type="submit" name="save_btn" class="save-btn" id="saveButton">Save</button>
        </div>
    </form>
</div>

<script>
    // 1. Tab Switching Logic
    function showCategory(catId) {
        document.querySelectorAll('.category-view').forEach(el => el.style.display = 'none');
        document.getElementById(catId).style.display = 'block';
        
        document.querySelectorAll('.sub-tab').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }
    
    // 2. Open Modal for ADDING (Clear the form)
    function openAddModal() {
        document.getElementById('activityForm').reset();
        document.getElementById('edit_id').value = ''; // Clear ID for new entry
        
        // Reset Title & Button
        document.getElementById('modalTitle').innerText = 'Add New <?php echo ucfirst($view); ?>';
        document.getElementById('saveButton').innerText = 'Save';

        document.getElementById('activityModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    // 3. Open Modal for EDITING (Populate the form)
    function openEditModal(button) {
        // Get the data stored in the button
        const data = JSON.parse(button.dataset.item);
        
        // Set the ID so PHP knows to Update
        document.getElementById('edit_id').value = data.id;

        // Change Title & Button text
        document.getElementById('modalTitle').innerText = 'Edit <?php echo ucfirst($view); ?>';
        document.getElementById('saveButton').innerText = 'Update';

        // Loop through data and fill inputs
        for (const key in data) {
            let inputName = key; // Default: Database column name matches Form input name

            // --- MAPPING LOGIC (Adjust if your DB names don't match Form names) ---
            // Example: If DB has 'due_date' but Form input is name="date"
            if (key === 'due_date') inputName = 'date';
            if (key === 'due_time') inputName = 'time';
            if (key === 'start_time') inputName = 'start_time'; 
            
            // Try to find the input field with this name
            const input = document.getElementsByName(inputName)[0];
            if (input) {
                input.value = data[key];
            }
        }

        document.getElementById('activityModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    // 4. Close Modal
    function closeModal() {
        document.getElementById('activityModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }
</script>

<?php 
// Helper function to render cards cleanly
function render_activity_list($items, $view, $cat) {
    if(empty($items)) {
        echo "<div class='empty-state'>No activities found in this category.</div>";
        return;
    }
    foreach($items as $item) {
        
        // --- 1. MAPPING DATABASE COLUMNS TO CARD DISPLAY ---
        $title = 'Untitled';
        $sub   = ''; // Subtitle (Details, Subject, etc.)

        if ($view == 'tasks') {
            // Table: tasks -> Column: title
            $title = $item['title'];
            $sub   = $item['subject'] ?? $item['details'] ?? ''; 
            
        } elseif ($view == 'extra') {
            // Table: extra_activities -> Column: title
            $title = $item['title'];
            $sub   = $item['category'] ?? ''; 
            
        } elseif ($view == 'classes') {
            // Table: classes -> Column: class_name
            $title = $item['class_name']; 
            $sub   = $item['subject'] ?? ''; // Show Subject as subtitle
            
        } elseif ($view == 'exams') {
            // Table: exams -> Column: exam_name
            $title = $item['exam_name']; 
            $sub   = $item['subject'] ?? ''; // Show Subject as subtitle
        }

        // --- 2. FORMAT DATE ---
        $date = $item['due_date'] ?? $item['date'] ?? 'No Date';
        
        // --- 3. PREPARE DATA FOR EDIT MODAL ---
        $itemJson = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
        
        // --- 4. RENDER HTML ---
        echo '<div class="activity-card">';
        
        // CARD INFO
        echo '  <div class="card-info">';
        echo '      <h3>'.htmlspecialchars($title).'</h3>';
        
        if(!empty($sub)) {
            echo '      <p>'.htmlspecialchars($sub).'</p>';
        }
        
        echo '      <small class="date-badge"><span class="material-icons-outlined" style="font-size:14px">event</span> '.$date.'</small>';
        echo '  </div>';
        
        // CARD ACTIONS
        echo '  <div class="card-actions">';
        
        // Complete Button
        echo '      <a href="complete_activity.php?id='.$item['id'].'&type='.$view.'" class="btn-icon complete" title="Complete" ><span class="material-icons-outlined" >check_circle</span></a>';
        
        // Edit Button
        echo '      <span class="btn-icon edit" title="Edit" onclick="openEditModal(this)" data-item="'.$itemJson.'"><span class="material-icons-outlined">edit</span></span>';
        
        // Delete Button
        echo '      <a href="delete_activity.php?id='.$item['id'].'&type='.$view.'" class="btn-icon delete" onclick="return confirm(\'Delete this?\')" title="Delete"><span class="material-icons-outlined">delete</span></a>';
        echo '  </div>';
        echo '</div>';
    }
}
include_once('layouts/footer.php'); 
?>