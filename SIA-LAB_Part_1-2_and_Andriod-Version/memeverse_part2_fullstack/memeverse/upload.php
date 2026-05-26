<?php
require_once 'includes/header.php';
if (!isLoggedIn()) redirect('login.php');

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="profile-header mb-4">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size: 3rem;">📤</div>
                <div>
                    <h2 class="mb-0">Share a Meme</h2>
                    <p class="text-muted mb-0">Spread the laughter! Upload your funniest memes 🎭</p>
                </div>
            </div>
        </div>
        
        <div class="post-card">
            <form id="upload-form" enctype="multipart/form-data" method="POST">
                <div class="post-body">
                    <!-- Image Preview Area -->
                    <div class="upload-preview-container text-center mb-4" id="preview-area">
                        <div id="preview-placeholder" class="upload-placeholder">
                            <i class="bi bi-cloud-upload"></i>
                            <p>Click to select an image</p>
                            <small class="text-muted">JPG, PNG, GIF, WEBP up to 5MB</small>
                        </div>
                        <div id="preview-container" style="display: none;">
                            <img id="preview-img" class="upload-preview" alt="Preview">
                            <button type="button" class="btn-remove-preview" id="remove-preview">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    
                    <input type="file" name="image" id="image-input" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp" required>
                    
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-fonts"></i> Title <span class="text-muted">(optional)</span></label>
                        <input type="text" name="title" id="post-title" class="form-control form-control-lg" placeholder="Give your meme a catchy title..." maxlength="200">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-text-paragraph"></i> Description <span class="text-muted">(optional)</span></label>
                        <textarea name="description" id="post-description" class="form-control" rows="4" placeholder="Tell us more about this meme..."></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-tags"></i> Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="post-category" class="form-select form-select-lg" required>
                            <option value="">-- Select a category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= getCategoryEmoji($cat['slug']) ?> <?= escape($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-create w-100" id="submit-upload">
                        <i class="bi bi-cloud-upload"></i> Publish Meme
                    </button>
                    
                    <div id="upload-message" class="mt-3" style="display: none;"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.upload-preview-container {
    position: relative;
    min-height: 250px;
    background: var(--bg-primary);
    border-radius: 20px;
    border: 2px dashed var(--border-color);
    transition: all 0.3s;
    cursor: pointer;
}
.upload-preview-container:hover { 
    border-color: var(--primary); 
    background: var(--bg-card); 
}
.upload-placeholder { 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: center; 
    padding: 3rem; 
    cursor: pointer; 
}
.upload-placeholder i { 
    font-size: 3rem; 
    color: var(--primary); 
    margin-bottom: 1rem; 
}
.upload-preview { 
    width: 100%; 
    max-height: 400px; 
    object-fit: contain; 
    border-radius: 16px; 
}
.btn-remove-preview { 
    position: absolute; 
    top: 10px; 
    right: 10px; 
    background: rgba(0,0,0,0.7); 
    border: none; 
    width: 32px; 
    height: 32px; 
    border-radius: 50%; 
    display: flex;
    align-items: center;
    justify-content: center;
    color: white; 
    cursor: pointer; 
    transition: all 0.2s;
}
.btn-remove-preview:hover { 
    background: var(--danger); 
    transform: scale(1.05);
}
.form-control, .form-select {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 0.75rem 1rem;
    color: var(--text-primary);
}
.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255,107,107,0.1);
    outline: none;
}
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.btn-create.w-100 {
    padding: 0.875rem;
    font-size: 1rem;
}
</style>

<script>
// Make sure siteBase is defined
const siteBase = '<?= SITE_URL ?>';

// DOM Elements
const imageInput = document.getElementById('image-input');
const previewContainer = document.getElementById('preview-container');
const previewPlaceholder = document.getElementById('preview-placeholder');
const previewImg = document.getElementById('preview-img');
const removePreviewBtn = document.getElementById('remove-preview');
const uploadForm = document.getElementById('upload-form');
const submitBtn = document.getElementById('submit-upload');
const messageDiv = document.getElementById('upload-message');

// Click preview area to select file
document.getElementById('preview-area').addEventListener('click', function(e) {
    // Don't trigger if clicking the remove button
    if (e.target === removePreviewBtn || removePreviewBtn.contains(e.target)) {
        return;
    }
    imageInput.click();
});

// Handle file selection
imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showMessage('Invalid file type. Please upload JPG, PNG, GIF, or WEBP.', 'error');
            imageInput.value = '';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showMessage('File too large. Maximum size is 5MB.', 'error');
            imageInput.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(ev) {
            previewImg.src = ev.target.result;
            previewContainer.style.display = 'block';
            previewPlaceholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
        showMessage('', 'clear'); // Clear any previous messages
    }
});

// Remove preview
removePreviewBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    imageInput.value = '';
    previewContainer.style.display = 'none';
    previewPlaceholder.style.display = 'flex';
    showMessage('', 'clear');
});

// Show message helper
function showMessage(message, type) {
    if (type === 'clear') {
        messageDiv.style.display = 'none';
        messageDiv.innerHTML = '';
        return;
    }
    messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} mt-3`;
    messageDiv.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
    messageDiv.style.display = 'block';
    
    if (type === 'success') {
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 3000);
    }
}

// Form submission
uploadForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validate file
    if (!imageInput.files || !imageInput.files[0]) {
        showMessage('Please select an image to upload', 'error');
        return;
    }
    
    // Validate category
    const category = document.getElementById('post-category').value;
    if (!category) {
        showMessage('Please select a category for your meme', 'error');
        return;
    }
    
    // Create FormData and append all fields
    const formData = new FormData();
    formData.append('image', imageInput.files[0]);
    formData.append('title', document.getElementById('post-title').value);
    formData.append('description', document.getElementById('post-description').value);
    formData.append('category_id', category);
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
    showMessage('', 'clear');
    
    try {
        console.log('Sending upload request to:', siteBase + 'api/upload.php');
        
        const response = await fetch(siteBase + 'api/upload.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            showMessage('🎉 Meme uploaded successfully! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = siteBase + 'post.php?id=' + data.post_id;
            }, 1500);
        } else {
            showMessage(data.error || 'Upload failed. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Publish Meme';
        }
    } catch (error) {
        console.error('Upload error:', error);
        showMessage('Network error: ' + error.message + '. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Publish Meme';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>