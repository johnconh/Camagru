<div class="editor-container">
    <div class="editor-main">
        <h2>📸 Create Photo</h2>
        <div id="message-container"></div>
        <div class="image-preview" id="imagePreview">
            <p class="preview-placeholder">Upload an image to start</p>
            <img id="previewImage" style="display: none;">
        </div>
        <div class="upload-section">
            <label for="imageUpload" class="upload-btn">
                📁 Choose Image
            </label>
            <input 
                type="file" 
                id="imageUpload" 
                accept="image/jpeg,image/jpg,image/png,image/gif"
                style="display: none;"
            >
            <p id="filename" class="filename-display"></p>
        </div>
        <div class="overlays-section">
            <h3>Select a Filter:</h3>
            <div class="overlays-grid" id="overlaysGrid">
                <?php if (empty($overlays)): ?>
                    <p class="no-overlays">No filters available</p>
                <?php else: ?>
                    <?php foreach ($overlays as $overlay): ?>
                        <div class="overlay-item" data-overlay="<?= htmlspecialchars($overlay['filename']) ?>">
                            <img src="<?= htmlspecialchars($overlay['path']) ?>" alt="Filter">
                            <div class="overlay-check">✓</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <button id="createBtn" class="btn btn-primary" disabled>
            Create Photo
        </button>
    </div>
    <div class="editor-sidebar">
        <h3>Your Photos</h3>
        <div class="user-photos" id="userPhotos">
            <?php if (empty($userPhotos)): ?>
                <p class="no-photos">No photos yet</p>
            <?php else: ?>
                <?php foreach ($userPhotos as $photo): ?>
                    <div class="photo-thumbnail" data-photo-id="<?= $photo['id'] ?>">
                        <img src="assets/images/uploads/<?= htmlspecialchars($photo['filename']) ?>" alt="Photo">
                        <div class="photo-overlay">
                            <button class="delete-photo-btn" data-photo-id="<?= $photo['id'] ?>">
                                🗑️
                            </button>
                        </div>
                        <div class="photo-stats">
                            ❤️ <?= $photo['likes_count'] ?> 
                            💬 <?= $photo['comments_count'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let selectedOverlay = null;
let uploadedImage = null;

const imageUpload = document.getElementById('imageUpload');
const previewImage = document.getElementById('previewImage');
const imagePreview = document.getElementById('imagePreview');
const createBtn = document.getElementById('createBtn');
const messageContainer = document.getElementById('message-container');
const filenameDisplay = document.getElementById('filename');

imageUpload.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        uploadedImage = file;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
            imagePreview.querySelector('.preview-placeholder')?.remove();
        };
        reader.readAsDataURL(file);
        
        filenameDisplay.textContent = file.name;
        checkCanCreate();
    }
});

document.querySelectorAll('.overlay-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.overlay-item').forEach(i => i.classList.remove('selected'));
        
        this.classList.add('selected');
        selectedOverlay = this.dataset.overlay;
        
        checkCanCreate();
    });
});

function checkCanCreate() {
    if (uploadedImage && selectedOverlay) {
        createBtn.disabled = false;
    } else {
        createBtn.disabled = true;
    }
}

createBtn.addEventListener('click', async function() {
    if (!uploadedImage || !selectedOverlay) {
        showMessage('Please upload an image and select a filter', 'error');
        return;
    }
    
    createBtn.disabled = true;
    createBtn.textContent = 'Creating...';
    
    const formData = new FormData();
    formData.append('image', uploadedImage);
    formData.append('overlay', selectedOverlay);
    
    try {
        const response = await fetch('index.php?page=photo-create', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message, 'success');
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showMessage(data.message, 'error');
        }
        
    } catch (error) {
        showMessage('An error occurred', 'error');
        console.error(error);
    } finally {
        createBtn.disabled = false;
        createBtn.textContent = 'Create Photo';
    }
});

document.querySelectorAll('.delete-photo-btn').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        
        if (!confirm('Are you sure you want to delete this photo?')) {
            return;
        }
        
        const photoId = this.dataset.photoId;
        
        try {
            const response = await fetch('index.php?page=photo-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'photo_id=' + photoId
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.closest('.photo-thumbnail').remove();
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
            }
            
        } catch (error) {
            showMessage('An error occurred', 'error');
            console.error(error);
        }
    });
});
</script>