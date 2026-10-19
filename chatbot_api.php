<?php
// 1. Check pathing: Ensure this path is correct relative to this file
require_once 'includes/config.php'; 

// 2. IMPORTANT: If require_user() redirects to a login page, 
// it will break the AJAX fetch. Only use it if you are sure the session is active.
// For testing, try commenting this line out:
// require_user();

// 3. Set header to return JSON
header('Content-Type: application/json');

// 4. Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if json_decode actually worked
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['reply' => 'Error: Invalid JSON input from JavaScript.']);
    exit;
}

$message = isset($data['message']) ? strtolower(trim($data['message'])) : '';

$response = "I'm sorry, I didn't quite catch that. You can ask me about **exams**, **tasks**, **timer**, or **settings**.";
$action = null; 

// --- THE LOGIC BRAIN ---

if (preg_match('/(hello|hi|hey|good morning)/', $message)) {
    $response = "Hello there! 👋 I am the StudyMate Bot. How can I help you organize your studies today?";
}
elseif (strpos($message, 'timer') !== false || strpos($message, 'focus') !== false || strpos($message, 'pomodoro') !== false) {
    $response = "The Focus Timer helps you stay productive! Would you like to start a session now?";
    $action = "timer.php";
}
elseif (strpos($message, 'exam') !== false || strpos($message, 'test') !== false) {
    $response = "You can manage your exam schedule in the Activities section. Do you want me to take you there?";
    $action = "activities.php#view-exams";
}
elseif (strpos($message, 'task') !== false || strpos($message, 'homework') !== false || strpos($message, 'assignment') !== false) {
    $response = "Keep track of your assignments in the Tasks list. Click below to view them.";
    $action = "activities.php#view-tasks";
}
elseif (strpos($message, 'password') !== false || strpos($message, 'reset') !== false || strpos($message, 'change') !== false) {
    $response = "You can update your password and security details in your Profile settings.";
    $action = "profile.php";
}
elseif (strpos($message, 'help') !== false || strpos($message, 'support') !== false) {
    $response = "I can answer basic questions! For more complex issues, please visit our Help Center.";
    $action = "help.php";
}
elseif (strpos($message, 'tired') !== false || strpos($message, 'motivat') !== false) { 
    $tips = [
        "Take a 5-minute break! Your brain needs rest to absorb information.",
        "Try the Pomodoro technique: 25 minutes of work, 5 minutes of rest.",
        "Drink some water! Hydration improves focus.",
        "You got this! One step at a time."
    ];
    $response = $tips[array_rand($tips)];
}

// Final output
echo json_encode([
    'reply' => $response,
    'action_link' => $action
]);