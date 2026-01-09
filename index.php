<?php
// Load URLs from JSON file
$urlsFile = __DIR__ . '/data/urls.json';
$allUrls = [];
if (file_exists($urlsFile)) {
    $content = file_get_contents($urlsFile);
    $allUrls = json_decode($content, true) ?: [];
}

// Check if user is logged in
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G3 URL Search Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
        .search-input {
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            font-size: 1.125rem;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }
        .search-button {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 1rem;
            font-size: 1.125rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }
        .result-item {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .result-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .result-title {
            color: #4f46e5;
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .result-url {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }
        .result-description {
            color: #475569;
            font-size: 1rem;
            line-height: 1.6;
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
<body class="min-h-screen flex items-center justify-center p-4 md:p-8">
    <!-- Decorative elements -->
    <div class="fixed top-20 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-50 float-animation"></div>
    <div class="fixed top-40 right-10 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-50 float-animation" style="animation-delay: 2s;"></div>
    <div class="fixed bottom-20 left-1/3 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-50 float-animation" style="animation-delay: 4s;"></div>

    <div class="relative z-10 w-full max-w-4xl">
        <div class="glass-card rounded-3xl shadow-2xl p-6 md:p-10">
            <!-- Navigation -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-4">
                    <?php if ($isLoggedIn): ?>
                        <span class="text-gray-600">Welcome, <strong><?php echo htmlspecialchars($userName); ?></strong></span>
                        <a href="logout.php" class="text-red-500 hover:text-red-700 text-sm font-medium">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Login</a>
                        <a href="register.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm">Register</a>
                    <?php endif; ?>
                </div>
                <a href="admin_panel.php" class="text-gray-500 hover:text-indigo-600 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Admin Panel
                </a>
            </div>
        <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-8">
            <span class="text-blue-600">G3 URL</span> Search
        </h1>

        <div class="flex flex-col sm:flex-row gap-4 mb-8">
            <input
                type="text"
                id="searchInput"
                placeholder="Enter keywords to search..."
                class="flex-grow search-input"
                aria-label="Search input"
            />
            <button id="searchButton" class="search-button">
                Search
            </button>
        </div>

        <div id="searchResults" class="space-y-6">
            <!-- Search results will be displayed here -->
            <p class="text-center text-gray-500 text-lg py-8" id="initialMessage">Start typing and click 'Search' to find URLs!</p>
        </div>
    </div>
    </div>

    <script>
        // Load URLs from PHP (which reads from JSON file)
        const allUrls = <?php echo json_encode($allUrls); ?>;


        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const searchResultsDiv = document.getElementById('searchResults');
        const initialMessage = document.getElementById('initialMessage');

        // Function to perform the search
        function performSearch() {
            const query = searchInput.value.toLowerCase().trim();
            searchResultsDiv.innerHTML = ''; // Clear previous results
            initialMessage.style.display = 'none'; // Hide initial message

            if (query === '') {
                searchResultsDiv.innerHTML = '<p class="text-center text-gray-500 text-lg py-8">Please enter a search term.</p>';
                return;
            }

            // In a real application, this would be an AJAX call to a PHP script
            // For example:
            // fetch('search.php?q=' + encodeURIComponent(query))
            //     .then(response => response.json())
            //     .then(data => {
            //         // Process and display data from the PHP backend
            //     })
            //     .catch(error => console.error('Error fetching search results:', error));

            // Simulate searching through the predefined URLs
            const filteredUrls = allUrls.filter(item =>
                item.title.toLowerCase().includes(query) ||
                item.description.toLowerCase().includes(query) ||
                item.url.toLowerCase().includes(query)
            );

            if (filteredUrls.length > 0) {
                filteredUrls.forEach(item => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'result-item'; // Apply Tailwind classes for styling

                    resultItem.innerHTML = `
                        <h3 class="result-title">
                            <a href="${item.url}" target="_blank" rel="noopener noreferrer" class="hover:underline">
                                ${item.title}
                            </a>
                        </h3>
                        <p class="result-url">${item.url}</p>
                        <p class="result-description">${item.description}</p>
                    `;
                    searchResultsDiv.appendChild(resultItem);
                });
            } else {
                searchResultsDiv.innerHTML = '<p class="text-center text-gray-500 text-lg py-8">No results found for your query.</p>';
            }
        }

        // Event listener for the search button click
        searchButton.addEventListener('click', performSearch);

        // Event listener for pressing 'Enter' in the search input
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        });

        // Optional: Focus on the search input when the page loads
        window.onload = () => {
            searchInput.focus();
        };
    </script>
</body>
</html>