// Wait for siteBase to be defined
(function() {
    // Function to wait for siteBase
    function waitForSiteBase(callback) {
        if (window.siteBase && window.siteBase !== 'undefined') {
            callback();
        } else {
            setTimeout(function() {
                waitForSiteBase(callback);
            }, 50);
        }
    }
    
    waitForSiteBase(function() {
        console.log('siteBase is ready:', window.siteBase);
        
        // ========== MOBILE SIDEBAR ==========
        function initMobileSidebar() {
            const hamburger = document.getElementById('hamburgerMenu');
            const sidebar = document.getElementById('mobileSidebar');
            const closeBtn = document.getElementById('closeSidebar');
            const overlay = document.getElementById('mobileSidebarOverlay');
            
            if (!hamburger || !sidebar || !closeBtn || !overlay) return;
            
            function openSidebar() {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            
            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            hamburger.addEventListener('click', openSidebar);
            closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);
        }
        
        // ========== VOTING SYSTEM ==========
        async function handleVote(postId, voteType) {
            const apiUrl = window.siteBase + 'api/vote.php';
            console.log('Vote API URL:', apiUrl);
            
            try {
                const formData = new URLSearchParams();
                formData.append('post_id', postId);
                formData.append('vote', voteType);
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData.toString()
                });
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    const voteCountSpan = document.getElementById(`vote-${postId}`);
                    if (voteCountSpan) {
                        voteCountSpan.textContent = data.new_score;
                    }
                    
                    const upBtn = document.querySelector(`.vote-btn.upvote[data-post-id="${postId}"]`);
                    const downBtn = document.querySelector(`.vote-btn.downvote[data-post-id="${postId}"]`);
                    
                    if (voteType === 'up') {
                        upBtn.classList.add('active');
                        downBtn.classList.remove('active');
                    } else {
                        downBtn.classList.add('active');
                        upBtn.classList.remove('active');
                    }
                    
                    return true;
                } else {
                    alert(data.error || 'Failed to vote');
                    return false;
                }
            } catch (error) {
                console.error('Vote error:', error);
                alert('Network error: ' + error.message);
                return false;
            }
        }
        
        // Global click handler for vote buttons
        document.addEventListener('click', async function(e) {
            const voteBtn = e.target.closest('.vote-btn');
            if (!voteBtn) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const postId = voteBtn.dataset.postId;
            const vote = voteBtn.dataset.vote;
            
            if (!postId || !vote) {
                console.error('Missing postId or vote type');
                return;
            }
            
            console.log(`Vote button clicked: postId=${postId}, vote=${vote}`);
            await handleVote(postId, vote);
        });
        
        // ========== REPORT HANDLER ==========
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.report-btn');
            if (!btn) return;
            
            e.preventDefault();
            
            const type = btn.dataset.type;
            const id = btn.dataset.id;
            const reason = prompt('Why are you reporting this?');
            
            if (!reason) return;
            
            try {
                const res = await fetch(window.siteBase + 'api/report.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({type, id, reason})
                });
                const data = await res.json();
                if (data.success) {
                    alert('Report submitted. Thank you!');
                } else {
                    alert(data.error || 'Failed to submit report');
                }
            } catch (err) {
                console.error('Report error:', err);
                alert('Network error');
            }
        });
        
        // Initialize
        initMobileSidebar();
        console.log('MemeVerse JS loaded successfully');
    });
})();