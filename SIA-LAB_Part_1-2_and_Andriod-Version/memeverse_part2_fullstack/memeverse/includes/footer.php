        </div>
    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <i class="bi bi-emoji-laughing"></i> MemeVerse
                    <p class="footer-tagline">Share the laughter, spread the joy 🎭</p>
                </div>
                <div class="footer-links">
                    <div class="footer-links-group">
                        <h6>Explore</h6>
                        <a href="<?= SITE_URL ?>">Home</a>
                        <a href="<?= SITE_URL ?>trending.php">Trending</a>
                        <a href="<?= SITE_URL ?>latest.php">Latest</a>
                    </div>
                    <div class="footer-links-group">
                        <h6>Information</h6>
                        <a href="<?= SITE_URL ?>about.php">About</a>
                        <a href="<?= SITE_URL ?>contact.php">Contact</a>
                        <a href="<?= SITE_URL ?>privacy.php">Privacy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> MemeVerse -- Where memes come to life.</p>
                <div class="footer-meme">
                    <span>Made with ❤️ and 😂</span>
                    <span class="footer-emoji">🎭</span>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= SITE_URL ?>assets/js/main.js"></script>
    
    <script>
    (function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (!darkModeToggle) return;
        
        function applyTheme(theme) {
            if (theme === 'dark') {
                document.body.classList.add('dark');
                darkModeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
            } else {
                document.body.classList.remove('dark');
                darkModeToggle.innerHTML = '<i class="bi bi-moon-stars-fill"></i>';
            }
            localStorage.setItem('memeverse_theme', theme);
        }
        
        const savedTheme = localStorage.getItem('memeverse_theme');
        applyTheme(savedTheme === 'dark' ? 'dark' : 'light');
        
        darkModeToggle.onclick = () => {
            const isDark = document.body.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        };
    })();
    </script>
    
    <script>
    const siteBase = '<?= SITE_URL ?>';
    const isUserLoggedIn = <?= json_encode((bool)$current_user); ?>;
    
    // Search functionality
    const searchInput = document.getElementById('navbar-search-input');
    const searchResults = document.getElementById('navbar-search-results');
    let searchTimeout;
    
    function doSearch() {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            searchResults.classList.remove('show');
            return;
        }
        searchResults.classList.add('show');
        searchResults.innerHTML = '<div class="search-loading"><div class="spinner"></div><div>Searching...</div></div>';
        
        fetch(siteBase + 'api/search.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => displaySearchResults(data, query))
            .catch(err => {
                console.error(err);
                searchResults.innerHTML = '<div class="search-empty"><i class="bi bi-wifi-off"></i>Network error</div>';
            });
    }
    
    function displaySearchResults(data, query) {
        let html = '';
        
        if (data.posts && data.posts.length) {
            html += '<div class="search-section"><div class="search-section-title"><i class="bi bi-image"></i> MEMES (' + data.posts.length + ')</div>';
            data.posts.forEach(post => {
                const imageUrl = post.image_path ? siteBase + post.image_path : '';
                html += `<a href="post.php?id=${post.id}" class="search-result-item">
                    <img src="${imageUrl}" class="search-result-img" onerror="this.src='https://via.placeholder.com/40'">
                    <div class="search-result-info">
                        <div class="search-result-title">${escapeHtml(post.title || 'Untitled')}</div>
                        <div class="search-result-meta">by ${escapeHtml(post.username)}</div>
                    </div>
                </a>`;
            });
            html += '</div>';
        }
        
        if (data.users && data.users.length) {
            if (data.posts && data.posts.length) html += '<div class="search-divider"></div>';
            html += '<div class="search-section"><div class="search-section-title"><i class="bi bi-people"></i> USERS (' + data.users.length + ')</div>';
            data.users.forEach(user => {
                const avatarUrl = user.avatar ? siteBase + 'avatars/' + user.avatar : 'https://ui-avatars.com/api/?background=ff6b6b&color=fff&size=36&name=' + encodeURIComponent(user.username);
                html += `<a href="profile.php?id=${user.id}" class="search-result-item">
                    <img src="${avatarUrl}" class="search-result-avatar">
                    <div class="search-result-info">
                        <div class="search-result-title">${escapeHtml(user.nickname || user.username)}</div>
                        <div class="search-result-meta">@${escapeHtml(user.username)}</div>
                    </div>
                </a>`;
            });
            html += '</div>';
        }
        
        if (!html) {
            html = '<div class="search-empty"><i class="bi bi-emoji-frown"></i> No results for "' + escapeHtml(query) + '"</div>';
        }
        searchResults.innerHTML = html;
    }
    
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(doSearch, 300);
    });
    
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.remove('show');
        }
    });
    
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Polling for notifications
    if (isUserLoggedIn) {
        async function fetchNotificationCount() {
            try {
                const res = await fetch(siteBase + 'api/unread_notifications.php');
                const data = await res.json();
                const badge = document.getElementById('unread-notification-count');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.textContent = '';
                        badge.style.display = 'none';
                    }
                }
            } catch (err) { console.error(err); }
        }
        
        async function fetchMessageCount() {
            try {
                const res = await fetch(siteBase + 'api/unread_messages.php');
                const data = await res.json();
                const badge = document.getElementById('unread-message-count');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.textContent = '';
                        badge.style.display = 'none';
                    }
                }
            } catch (err) { console.error(err); }
        }
        
        setInterval(fetchNotificationCount, 3000);
        setInterval(fetchMessageCount, 3000);
        fetchNotificationCount();
        fetchMessageCount();
    }
    </script>
</body>
</html>