<?php
require_once 'auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: admin_panel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            header('Location: admin_panel.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - G3 URL Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <!-- Decorative elements -->
    <div class="absolute top-20 left-20 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 float-animation"></div>
    <div class="absolute top-40 right-20 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 float-animation" style="animation-delay: 2s;"></div>
    <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 float-animation" style="animation-delay: 4s;"></div>

    <div class="relative z-10 w-full max-w-md">
        <div class="glass-card rounded-3xl shadow-2xl p-8 md:p-10">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mb-4 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Welcome Back</h1>
                <p class="text-gray-500 mt-2">Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none input-focus transition-all duration-300"
                            placeholder="you@example.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none input-focus transition-all duration-300"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl btn-hover shadow-lg">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Don't have an account?
                    <a href="register.php" class="text-indigo-600 font-semibold hover:text-indigo-800 transition-colors">Sign up</a>
                </p>
            </div>

            <div class="mt-6 text-center">
                <a href="index.php" class="inline-flex items-center text-gray-500 hover:text-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Search
                </a>
            </div>
        </div>

        <!-- Demo credentials hint -->
        <div class="mt-6 text-center">
            <div class="inline-block bg-white/20 backdrop-blur-sm rounded-xl px-6 py-3">
                <p class="text-white/90 text-sm">
                    <span class="font-semibold">Demo Admin:</span> admin@example.com / password
                </p>
            </div>
        </div>
    </div>
</body>
</html>
