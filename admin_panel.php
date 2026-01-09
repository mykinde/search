<?php
require_once 'auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$users = getUsers();
$urls = getUrls();
$success = '';
$error = '';

// Handle URL actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_url' && isAdmin()) {
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (!empty($title) && !empty($url)) {
            $newId = empty($urls) ? 1 : max(array_column($urls, 'id')) + 1;
            $urls[] = [
                'id' => $newId,
                'title' => $title,
                'url' => $url,
                'description' => $description
            ];
            saveUrls($urls);
            $success = 'URL added successfully';
        } else {
            $error = 'Title and URL are required';
        }
    }
    
    if ($action === 'delete_url' && isAdmin()) {
        $urlId = (int)($_POST['url_id'] ?? 0);
        $urls = array_filter($urls, fn($u) => $u['id'] !== $urlId);
        $urls = array_values($urls);
        saveUrls($urls);
        $success = 'URL deleted successfully';
    }
    
    if ($action === 'edit_url' && isAdmin()) {
        $urlId = (int)($_POST['url_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (!empty($title) && !empty($url)) {
            foreach ($urls as &$urlItem) {
                if ($urlItem['id'] === $urlId) {
                    $urlItem['title'] = $title;
                    $urlItem['url'] = $url;
                    $urlItem['description'] = $description;
                    break;
                }
            }
            saveUrls($urls);
            $success = 'URL updated successfully';
        } else {
            $error = 'Title and URL are required';
        }
    }
    
    if ($action === 'delete_user' && isAdmin()) {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId !== $user['id']) {
            $users = array_filter($users, fn($u) => $u['id'] !== $userId);
            $users = array_values($users);
            saveUsers($users);
            $success = 'User deleted successfully';
        } else {
            $error = 'Cannot delete your own account';
        }
    }
    
    if ($action === 'change_role' && isAdmin()) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newRole = $_POST['new_role'] ?? 'user';
        foreach ($users as &$u) {
            if ($u['id'] === $userId) {
                $u['role'] = $newRole;
                break;
            }
        }
        saveUsers($users);
        $success = 'User role updated';
    }
    
    if ($action === 'reset_password' && isAdmin()) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword)) {
            $error = 'Password is required';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            foreach ($users as &$u) {
                if ($u['id'] === $userId) {
                    $u['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
                    break;
                }
            }
            saveUsers($users);
            $success = 'Password updated successfully';
        }
    }
    
    // Refresh data
    $users = getUsers();
    $urls = getUrls();
}

// URL Search and Pagination
$urlSearch = trim($_GET['url_search'] ?? '');
$urlPage = max(1, (int)($_GET['url_page'] ?? 1));
$urlsPerPage = 50;

// Filter URLs by search term
$filteredUrls = $urls;
if (!empty($urlSearch)) {
    $filteredUrls = array_filter($urls, function($urlItem) use ($urlSearch) {
        $search = strtolower($urlSearch);
        return strpos(strtolower($urlItem['title']), $search) !== false ||
               strpos(strtolower($urlItem['url']), $search) !== false ||
               strpos(strtolower($urlItem['description'] ?? ''), $search) !== false;
    });
    $filteredUrls = array_values($filteredUrls);
}

// Calculate pagination
$totalUrls = count($filteredUrls);
$totalUrlPages = max(1, ceil($totalUrls / $urlsPerPage));
$urlPage = min($urlPage, $totalUrlPages);
$urlOffset = ($urlPage - 1) * $urlsPerPage;
$paginatedUrls = array_slice($filteredUrls, $urlOffset, $urlsPerPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - G3 URL Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%); }
        .card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .table-row:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar w-64 text-white p-6 hidden lg:block">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">G3 Search</h1>
                <p class="text-indigo-200 text-sm">Admin Panel</p>
            </div>
            <nav class="space-y-2">
                <a href="#dashboard" class="flex items-center px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="index.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/20">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Search Page
                </a>
                <a href="logout.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-white/20">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 lg:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p class="text-gray-500">Role: <span class="font-semibold capitalize"><?php echo htmlspecialchars($user['role']); ?></span></p>
                </div>
                <a href="logout.php" class="lg:hidden bg-red-500 text-white px-4 py-2 rounded-lg">Logout</a>
            </div>

            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
                    <span class="text-green-700 font-medium"><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <span class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="stat-card text-white p-6 rounded-2xl">
                    <div class="text-4xl font-bold"><?php echo count($urls); ?></div>
                    <div class="text-indigo-100">Total URLs</div>
                </div>
                <div class="card p-6">
                    <div class="text-4xl font-bold text-indigo-600"><?php echo count($users); ?></div>
                    <div class="text-gray-500">Total Users</div>
                </div>
                <div class="card p-6">
                    <div class="text-4xl font-bold text-purple-600"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></div>
                    <div class="text-gray-500">Admins</div>
                </div>
            </div>

            <?php if (isAdmin()): ?>
            <!-- Add URL Form -->
            <div class="card p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Add New URL</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="hidden" name="action" value="add_url">
                    <input type="text" name="title" placeholder="Title" required class="border-2 border-gray-200 rounded-xl px-4 py-2 focus:border-indigo-500 focus:outline-none">
                    <input type="url" name="url" placeholder="https://example.com" required class="border-2 border-gray-200 rounded-xl px-4 py-2 focus:border-indigo-500 focus:outline-none">
                    <input type="text" name="description" placeholder="Description" class="border-2 border-gray-200 rounded-xl px-4 py-2 focus:border-indigo-500 focus:outline-none">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-xl hover:bg-indigo-700 font-semibold">Add URL</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- URLs Table -->
            <div class="card p-6 mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <h3 class="text-xl font-bold text-gray-800">URLs Database</h3>
                    <form method="GET" class="flex items-center gap-2">
                        <div class="relative">
                            <input type="text" name="url_search" value="<?php echo htmlspecialchars($urlSearch); ?>" 
                                placeholder="Search URLs..." 
                                class="border-2 border-gray-200 rounded-xl px-4 py-2 pl-10 focus:border-indigo-500 focus:outline-none w-64">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 font-medium">Search</button>
                        <?php if (!empty($urlSearch)): ?>
                        <a href="admin_panel.php" class="text-gray-500 hover:text-gray-700 px-3 py-2">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Results info -->
                <div class="text-sm text-gray-500 mb-4">
                    Showing <?php echo $urlOffset + 1; ?> - <?php echo min($urlOffset + $urlsPerPage, $totalUrls); ?> of <?php echo $totalUrls; ?> URLs
                    <?php if (!empty($urlSearch)): ?>
                    <span class="text-indigo-600">(filtered by "<?php echo htmlspecialchars($urlSearch); ?>")</span>
                    <?php endif; ?>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-100">
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Title</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">URL</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Description</th>
                                <?php if (isAdmin()): ?><th class="text-left py-3 px-4 font-semibold text-gray-600">Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paginatedUrls)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">No URLs found</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($paginatedUrls as $urlItem): ?>
                            <tr class="table-row border-b border-gray-50">
                                <td class="py-3 px-4"><?php echo $urlItem['id']; ?></td>
                                <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($urlItem['title']); ?></td>
                                <td class="py-3 px-4"><a href="<?php echo htmlspecialchars($urlItem['url']); ?>" target="_blank" class="text-indigo-600 hover:underline"><?php echo htmlspecialchars($urlItem['url']); ?></a></td>
                                <td class="py-3 px-4 text-gray-500"><?php echo htmlspecialchars($urlItem['description'] ?? ''); ?></td>
                                <?php if (isAdmin()): ?>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($urlItem)); ?>)" class="text-indigo-500 hover:text-indigo-700 font-medium">Edit</button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this URL?')">
                                            <input type="hidden" name="action" value="delete_url">
                                            <input type="hidden" name="url_id" value="<?php echo $urlItem['id']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalUrlPages > 1): ?>
                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
                    <div class="text-sm text-gray-500">
                        Page <?php echo $urlPage; ?> of <?php echo $totalUrlPages; ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($urlPage > 1): ?>
                        <a href="?url_page=1<?php echo $urlSearch ? '&url_search=' . urlencode($urlSearch) : ''; ?>" 
                           class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium">First</a>
                        <a href="?url_page=<?php echo $urlPage - 1; ?><?php echo $urlSearch ? '&url_search=' . urlencode($urlSearch) : ''; ?>" 
                           class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium">Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $urlPage - 2);
                        $endPage = min($totalUrlPages, $urlPage + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                        <a href="?url_page=<?php echo $i; ?><?php echo $urlSearch ? '&url_search=' . urlencode($urlSearch) : ''; ?>" 
                           class="px-3 py-2 rounded-lg text-sm font-medium <?php echo $i === $urlPage ? 'bg-indigo-600 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($urlPage < $totalUrlPages): ?>
                        <a href="?url_page=<?php echo $urlPage + 1; ?><?php echo $urlSearch ? '&url_search=' . urlencode($urlSearch) : ''; ?>" 
                           class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium">Next</a>
                        <a href="?url_page=<?php echo $totalUrlPages; ?><?php echo $urlSearch ? '&url_search=' . urlencode($urlSearch) : ''; ?>" 
                           class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium">Last</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (isAdmin()): ?>
            <!-- Users Table -->
            <div class="card p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Users</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-100">
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Name</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Email</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Role</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr class="table-row border-b border-gray-50">
                                <td class="py-3 px-4"><?php echo $u['id']; ?></td>
                                <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($u['name']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <form method="POST" class="inline-flex items-center gap-1">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <select name="new_role" class="border rounded px-2 py-1 text-sm">
                                                <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <button type="submit" class="text-indigo-500 hover:text-indigo-700 text-sm font-medium">Update</button>
                                        </form>
                                        <button type="button" onclick="openPasswordModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>')" class="text-amber-500 hover:text-amber-700 text-sm font-medium">Reset Password</button>
                                        <?php if ($u['id'] !== $user['id']): ?>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Delete</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Edit URL Modal -->
    <?php if (isAdmin()): ?>
    <div id="editModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 opacity-0" id="editModalContent">
            <div class="p-6 border-b border-gray-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">Edit URL</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="edit_url">
                <input type="hidden" name="url_id" id="edit_url_id">
                
                <div>
                    <label for="edit_title" class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" id="edit_title" required 
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-indigo-500 focus:outline-none transition-colors">
                </div>
                
                <div>
                    <label for="edit_url" class="block text-sm font-semibold text-gray-700 mb-2">URL</label>
                    <input type="url" name="url" id="edit_url" required 
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-indigo-500 focus:outline-none transition-colors">
                </div>
                
                <div>
                    <label for="edit_description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-indigo-500 focus:outline-none transition-colors resize-none"></textarea>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeEditModal()" 
                        class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(urlData) {
            const modal = document.getElementById('editModal');
            const modalContent = document.getElementById('editModalContent');
            
            // Populate form fields
            document.getElementById('edit_url_id').value = urlData.id;
            document.getElementById('edit_title').value = urlData.title;
            document.getElementById('edit_url').value = urlData.url;
            document.getElementById('edit_description').value = urlData.description || '';
            
            // Show modal with animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Trigger animation
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        
        function closeEditModal() {
            const modal = document.getElementById('editModal');
            const modalContent = document.getElementById('editModalContent');
            
            // Animate out
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            // Hide modal after animation
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
        
        // Close modal when clicking outside
        document.getElementById('editModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closePasswordModal();
            }
        });
    </script>

    <!-- Password Reset Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="passwordModalContent">
            <div class="p-6 border-b border-gray-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">Reset Password</h3>
                    <button type="button" onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-500 text-sm mt-1">Reset password for: <span id="password_user_name" class="font-semibold text-gray-700"></span></p>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="password_user_id">
                
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <input type="password" name="new_password" id="new_password" required minlength="6"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-amber-500 focus:outline-none transition-colors"
                        placeholder="••••••••">
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-amber-500 focus:outline-none transition-colors"
                        placeholder="••••••••">
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closePasswordModal()" 
                        class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="flex-1 px-6 py-3 bg-amber-500 text-white rounded-xl font-semibold hover:bg-amber-600 transition-colors shadow-lg shadow-amber-200">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPasswordModal(userId, userName) {
            const modal = document.getElementById('passwordModal');
            const modalContent = document.getElementById('passwordModalContent');
            
            // Populate form fields
            document.getElementById('password_user_id').value = userId;
            document.getElementById('password_user_name').textContent = userName;
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            
            // Show modal with animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Trigger animation
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Focus on password field
            setTimeout(() => {
                document.getElementById('new_password').focus();
            }, 100);
        }
        
        function closePasswordModal() {
            const modal = document.getElementById('passwordModal');
            const modalContent = document.getElementById('passwordModalContent');
            
            if (!modal) return;
            
            // Animate out
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            // Hide modal after animation
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
        
        // Close password modal when clicking outside
        document.getElementById('passwordModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
