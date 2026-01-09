<?php
session_start();

define('USERS_FILE', __DIR__ . '/data/users.json');
define('URLS_FILE', __DIR__ . '/data/urls.json');

/**
 * Get all users from JSON file
 */
function getUsers() {
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $content = file_get_contents(USERS_FILE);
    return json_decode($content, true) ?: [];
}

/**
 * Save users to JSON file
 */
function saveUsers($users) {
    $dir = dirname(USERS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

/**
 * Get all URLs from JSON file
 */
function getUrls() {
    if (!file_exists(URLS_FILE)) {
        return [];
    }
    $content = file_get_contents(URLS_FILE);
    return json_decode($content, true) ?: [];
}

/**
 * Save URLs to JSON file
 */
function saveUrls($urls) {
    $dir = dirname(URLS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(URLS_FILE, json_encode($urls, JSON_PRETTY_PRINT));
}

/**
 * Register a new user
 */
function registerUser($name, $email, $password, $role = 'user') {
    $users = getUsers();
    
    // Check if email already exists
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
    }
    
    // Generate new ID
    $newId = 1;
    if (!empty($users)) {
        $maxId = max(array_column($users, 'id'));
        $newId = $maxId + 1;
    }
    
    // Create new user
    $newUser = [
        'id' => $newId,
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'role' => $role,
        'created_at' => date('c')
    ];
    
    $users[] = $newUser;
    saveUsers($users);
    
    return ['success' => true, 'message' => 'Registration successful'];
}

/**
 * Login user
 */
function loginUser($email, $password) {
    $users = getUsers();
    
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                return ['success' => true, 'message' => 'Login successful'];
            }
        }
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}
?>
