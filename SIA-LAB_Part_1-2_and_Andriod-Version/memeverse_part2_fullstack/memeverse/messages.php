<?php
require_once 'includes/header.php';
if (!isLoggedIn()) redirect('login.php');
$user_id = $_SESSION['user_id'];
?>

<div class="container mt-4">
    <div class="row g-4">
        <!-- Conversations List -->
        <div class="col-md-4">
            <div class="post-card">
                <div class="post-header">
                    <i class="bi bi-chat-dots fs-4"></i>
                    <h3 class="mb-0">Messages</h3>
                </div>
                <div class="post-body p-0">
                    <div id="conversations-list" class="conversations-list">
                        <div class="text-center py-4 text-muted">Loading conversations...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chat Area -->
        <div class="col-md-8">
            <div id="chat-container" class="post-card" style="display: none;">
                <div class="post-header" id="chat-header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-2">
                            <img src="" id="chat-avatar" class="rounded-circle" width="40" height="40">
                            <div>
                                <h5 class="mb-0" id="chat-username">Select a conversation</h5>
                                <small class="text-muted" id="chat-status"></small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger delete-conversation-btn" style="display: none;">
                            <i class="bi bi-trash"></i> Delete Conversation
                        </button>
                    </div>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <div class="text-center py-5 text-muted">Select a conversation to start chatting</div>
                </div>
                <div class="chat-input-area">
                    <div class="input-group">
                        <input type="text" id="message-input" class="form-control" placeholder="Type a message..." autocomplete="off">
                        <button class="btn btn-primary" id="send-btn">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                </div>
            </div>
            <div id="no-chat-selected" class="post-card text-center py-5">
                <i class="bi bi-chat-dots fs-1 text-muted"></i>
                <p class="mt-3">Select a conversation to start messaging</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Conversation Modal -->
<div class="modal fade" id="deleteConversationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="confirmation-icon"><i class="bi bi-trash3"></i></div>
                <h5 class="confirmation-title">Delete Conversation?</h5>
                <p class="confirmation-message">All messages with this user will be permanently deleted.</p>
                <div class="confirmation-buttons">
                    <button class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger confirm-delete-btn">Delete Forever</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.conversations-list {
    max-height: 600px;
    overflow-y: auto;
}
.conversation-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: background 0.2s;
}
.conversation-item:hover {
    background: var(--bg-primary);
}
.conversation-item.active {
    background: var(--primary-light);
    border-left: 3px solid var(--primary);
}
.conversation-info {
    flex: 1;
    min-width: 0;
}
.conversation-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.2rem;
}
.conversation-preview {
    font-size: 0.8rem;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conversation-time {
    font-size: 0.7rem;
    color: var(--text-muted);
}
.chat-messages {
    height: 450px;
    overflow-y: auto;
    padding: 1rem;
    background: var(--bg-primary);
}
.message {
    display: flex;
    margin-bottom: 1rem;
}
.message-out {
    justify-content: flex-end;
}
.message-in {
    justify-content: flex-start;
}
.message-bubble {
    max-width: 70%;
    padding: 0.5rem 1rem;
    border-radius: 18px;
    position: relative;
}
.message-out .message-bubble {
    background: var(--primary);
    color: white;
    border-bottom-right-radius: 4px;
}
.message-in .message-bubble {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    border-bottom-left-radius: 4px;
}
.message-text {
    word-wrap: break-word;
}
.message-time {
    font-size: 0.65rem;
    margin-top: 0.25rem;
    opacity: 0.7;
    text-align: right;
}
.chat-input-area {
    padding: 1rem;
    border-top: 1px solid var(--border-color);
    background: var(--bg-card);
}
.confirmation-icon {
    width: 70px;
    height: 70px;
    background: rgba(239, 71, 111, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    color: var(--danger);
}
.confirmation-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.confirmation-message {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}
.confirmation-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}
.btn-cancel {
    background: transparent;
    border: 1px solid var(--border-color);
    padding: 0.5rem 1.5rem;
    border-radius: 40px;
    color: var(--text-secondary);
    font-weight: 500;
}
.btn-cancel:hover {
    background: var(--bg-primary);
    border-color: var(--primary);
    color: var(--primary);
}
@media (max-width: 768px) {
    .chat-messages { height: 350px; }
}
</style>

<script>
// Make sure siteBase is defined
window.siteBase = window.siteBase || '<?= SITE_URL ?>';
const currentUserId = <?= $user_id ?>;

console.log('Messages page loaded, siteBase:', window.siteBase);
console.log('Current user ID:', currentUserId);

let currentChatWith = null;
let pollInterval = null;
let conversationToDelete = null;

// ========== LOAD CONVERSATIONS ==========
async function loadConversations() {
    try {
        console.log('Loading conversations...');
        const response = await fetch(window.siteBase + 'api/get_conversations.php');
        const data = await response.json();
        console.log('Conversations response:', data);
        
        if (!data.success) {
            console.error('Failed to load conversations:', data.error);
            return;
        }
        
        const container = document.getElementById('conversations-list');
        
        if (!data.conversations || data.conversations.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-muted">No conversations yet. Start by messaging someone from their profile!</div>';
            return;
        }
        
        let html = '';
        for (const c of data.conversations) {
            const activeClass = currentChatWith === c.id ? 'active' : '';
            const lastMsg = c.last_msg ? escapeHtml(c.last_msg.substring(0, 50)) : 'No messages yet';
            const lastTime = c.last_time || '';
            const unreadBadge = c.unread > 0 ? `<span class="badge bg-danger ms-2">${c.unread}</span>` : '';
            
            html += `
                <div class="conversation-item ${activeClass}" data-user-id="${c.id}">
                    <img src="${c.avatar_url}" class="rounded-circle" width="48" height="48">
                    <div class="conversation-info">
                        <div class="conversation-name">${escapeHtml(c.nickname || c.username)}</div>
                        <div class="conversation-preview">${lastMsg}</div>
                        <div class="conversation-time">${lastTime}</div>
                    </div>
                    <div class="conversation-badge">${unreadBadge}</div>
                </div>
            `;
        }
        container.innerHTML = html;
        
        // Attach click handlers
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', () => {
                const userId = parseInt(item.dataset.userId);
                openChat(userId);
            });
        });
        
    } catch (error) {
        console.error('loadConversations error:', error);
        document.getElementById('conversations-list').innerHTML = '<div class="text-center py-4 text-danger">Failed to load conversations</div>';
    }
}

// ========== OPEN CHAT ==========
async function openChat(userId) {
    console.log('Opening chat with user:', userId);
    
    // Stop previous polling
    if (pollInterval) {
        clearInterval(pollInterval);
    }
    
    currentChatWith = userId;
    
    // Update active state in conversation list
    document.querySelectorAll('.conversation-item').forEach(item => {
        if (parseInt(item.dataset.userId) === userId) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
    
    // Fetch user info for header
    try {
        const response = await fetch(window.siteBase + `api/get_user.php?id=${userId}`);
        const data = await response.json();
        console.log('User info:', data);
        
        if (data.success && data.user) {
            document.getElementById('chat-avatar').src = data.user.avatar_url;
            document.getElementById('chat-username').innerText = data.user.nickname || data.user.username;
        }
    } catch (error) {
        console.error('Error fetching user info:', error);
    }
    
    // Show chat container
    document.getElementById('chat-container').style.display = 'block';
    document.getElementById('no-chat-selected').style.display = 'none';
    document.querySelector('.delete-conversation-btn').style.display = 'inline-block';
    
    // Load messages
    await loadMessages(userId);
    
    // Start polling for new messages
    pollInterval = setInterval(() => loadMessages(userId), 3000);
}

// ========== LOAD MESSAGES ==========
async function loadMessages(userId) {
    try {
        const response = await fetch(window.siteBase + `api/get_messages.php?with=${userId}`);
        const data = await response.json();
        
        if (!data.success) {
            console.error('Failed to load messages:', data.error);
            return;
        }
        
        const container = document.getElementById('chat-messages');
        
        if (!data.messages || data.messages.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted">No messages yet. Send a message to start the conversation!</div>';
            return;
        }
        
        let html = '';
        for (const m of data.messages) {
            const isMe = m.sender_id === currentUserId;
            html += `
                <div class="message ${isMe ? 'message-out' : 'message-in'}">
                    <div class="message-bubble">
                        <div class="message-text">${escapeHtml(m.message)}</div>
                        <div class="message-time">${timeAgo(m.created_at)}</div>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
        
        // Refresh conversation list to update unread badges
        loadConversations();
        
    } catch (error) {
        console.error('loadMessages error:', error);
    }
}

// ========== SEND MESSAGE ==========
async function sendMessage() {
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message || !currentChatWith) {
        console.log('Cannot send: no message or no chat selected');
        return;
    }
    
    console.log('Sending message to:', currentChatWith, 'message:', message);
    
    try {
        const response = await fetch(window.siteBase + 'api/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: currentChatWith,
                message: message
            })
        });
        
        const data = await response.json();
        console.log('Send message response:', data);
        
        if (data.success) {
            input.value = '';
            await loadMessages(currentChatWith);
            await loadConversations();
        } else {
            alert(data.error || 'Failed to send message');
        }
    } catch (error) {
        console.error('Send message error:', error);
        alert('Network error. Please try again.');
    }
}

// ========== DELETE CONVERSATION ==========
async function deleteConversation() {
    if (!currentChatWith) return;
    
    try {
        const response = await fetch(window.siteBase + 'api/delete_conversation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: currentChatWith
            })
        });
        
        const data = await response.json();
        console.log('Delete conversation response:', data);
        
        if (data.success) {
            // Close current chat
            currentChatWith = null;
            document.getElementById('chat-container').style.display = 'none';
            document.getElementById('no-chat-selected').style.display = 'block';
            document.querySelector('.delete-conversation-btn').style.display = 'none';
            
            // Reload conversations list
            await loadConversations();
            
            // Stop polling
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        } else {
            alert(data.error || 'Failed to delete conversation');
        }
    } catch (error) {
        console.error('Delete conversation error:', error);
        alert('Network error. Please try again.');
    }
}

// ========== HELPER FUNCTIONS ==========
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
    return date.toLocaleDateString();
}

// ========== EVENT LISTENERS ==========
document.getElementById('send-btn').addEventListener('click', sendMessage);
document.getElementById('message-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});

document.querySelector('.delete-conversation-btn')?.addEventListener('click', () => {
    if (currentChatWith) {
        const modal = new bootstrap.Modal(document.getElementById('deleteConversationModal'));
        modal.show();
    }
});

document.querySelector('.confirm-delete-btn')?.addEventListener('click', async () => {
    bootstrap.Modal.getInstance(document.getElementById('deleteConversationModal')).hide();
    await deleteConversation();
});

// ========== CHECK URL PARAMETER FOR DIRECT MESSAGE ==========
const urlParams = new URLSearchParams(window.location.search);
const directUserId = urlParams.get('with');
if (directUserId) {
    console.log('Direct message to user:', directUserId);
    setTimeout(() => {
        openChat(parseInt(directUserId));
    }, 1000);
}

// ========== INITIAL LOAD ==========
loadConversations();
setInterval(loadConversations, 5000);

console.log('Messages page initialized');
</script>

<?php require_once 'includes/footer.php'; ?>