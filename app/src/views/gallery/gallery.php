<!-- Gallery Public -->
<div class="gallery-container">
    <h2>📸 Gallery</h2>
    <p style="text-align: center; color: #666; margin-bottom: 30px;">
        Photos from all users
    </p>
    
    <div id="message-container"></div>
    
    <!-- Grid de fotos -->
    <div class="gallery-grid" id="galleryGrid">
        <?php if (empty($photos)): ?>
            <p style="text-align: center; grid-column: 1/-1; color: #999;">No photos yet</p>
        <?php else: ?>
            <?php foreach ($photos as $photo): ?>
                <div class="gallery-photo-card">
                    <div class="photo-image">
                        <img src="assets/images/uploads/<?= htmlspecialchars($photo['filename']) ?>" alt="Photo">
                    </div>
                    
                    <!-- Header con username y fecha -->
                    <div class="photo-header">
                        <span class="username"><?= htmlspecialchars($photo['username']) ?></span>
                        <span class="date"><?= date('M d, Y', strtotime($photo['created_at'])) ?></span>
                    </div>
                    
                    <!-- Stats (likes y comentarios) -->
                    <div class="photo-stats">
                        <span class="stat">❤️ <span class="likes-count"><?= $photo['likes_count'] ?></span></span>
                        <span class="stat">💬 <?= count($photo['comments']) ?></span>
                    </div>
                    
                    <!-- Like button (solo si está logueado) -->
                    <?php if ($userId): ?>
                        <button class="like-btn <?= $photo['liked_by_me'] ? 'liked' : '' ?>" data-photo-id="<?= $photo['id'] ?>">
                            <?= $photo['liked_by_me'] ? '❤️ Unlike' : '🤍 Like' ?>
                        </button>
                    <?php else: ?>
                        <p style="color: #999; font-size: 0.9em;">
                            <a href="index.php?page=login">Login</a> to like photos
                        </p>
                    <?php endif; ?>
                    
                    <!-- Comments section -->
                    <div class="comments-section">
                        <div class="comments-list" id="comments-<?= $photo['id'] ?>">
                            <?php foreach ($photo['comments'] as $comment): ?>
                                <div class="comment">
                                    <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                    <?= htmlspecialchars($comment['content']) ?>
                                    <span class="comment-date"><?= date('M d', strtotime($comment['created_at'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Add comment (solo si está logueado) -->
                        <?php if ($userId): ?>
                            <form class="comment-form" data-photo-id="<?= $photo['id'] ?>">
                                <input 
                                    type="text" 
                                    placeholder="Add a comment..." 
                                    class="comment-input"
                                    required
                                >
                                <button type="submit" class="comment-btn">Post</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="index.php?page=gallery&page=<?= $page - 1 ?>" class="page-btn">← Previous</a>
            <?php endif; ?>
            
            <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="index.php?page=gallery&page=<?= $page + 1 ?>" class="page-btn">Next →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.gallery-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
}

.gallery-container h2 {
    text-align: center;
    margin-bottom: 10px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.gallery-photo-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.photo-image {
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: #f5f5f5;
}

.photo-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.username {
    font-weight: 600;
    color: #333;
}

.date {
    font-size: 0.85em;
    color: #999;
}

.photo-stats {
    padding: 10px 15px;
    display: flex;
    gap: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.stat {
    font-size: 0.9em;
}

.like-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.like-btn:hover {
    background: #5568d3;
}

.like-btn.liked {
    background: #e24b4a;
}

.comments-section {
    padding: 15px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.comments-list {
    flex: 1;
    overflow-y: auto;
    max-height: 150px;
    margin-bottom: 12px;
}

.comment {
    font-size: 0.85em;
    margin-bottom: 8px;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 5px;
}

.comment strong {
    color: #333;
}

.comment-date {
    font-size: 0.75em;
    color: #999;
    margin-left: 8px;
}

.comment-form {
    display: flex;
    gap: 8px;
}

.comment-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9em;
}

.comment-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.comment-btn:hover {
    background: #5568d3;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 40px;
}

.page-btn {
    background: #667eea;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
}

.page-btn:hover {
    background: #5568d3;
    transform: translateY(-2px);
}

.page-info {
    color: #666;
    font-weight: 500;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .comments-list {
        max-height: 100px;
    }
}
</style>

<script>
// Like button con AJAX
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const photoId = this.dataset.photoId;
        const likeSpan = this.closest('.gallery-photo-card').querySelector('.likes-count');
        
        try {
            const response = await fetch('index.php?page=gallery-toggle-like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'photo_id=' + photoId
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar número de likes
                likeSpan.textContent = data.data.likes_count;
                
                // Cambiar estado del botón
                if (data.data.action === 'added') {
                    this.classList.add('liked');
                    this.textContent = '❤️ Unlike';
                } else {
                    this.classList.remove('liked');
                    this.textContent = '🤍 Like';
                }
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});

// Comment form con AJAX
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const photoId = this.dataset.photoId;
        const input = this.querySelector('.comment-input');
        const content = input.value;
        
        try {
            const response = await fetch('index.php?page=gallery-add-comment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'photo_id=' + photoId + '&content=' + encodeURIComponent(content)
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Añadir comentario al DOM
                const commentsList = document.getElementById('comments-' + photoId);
                const comment = document.createElement('div');
                comment.className = 'comment';
                comment.innerHTML = `
                    <strong>${data.data.comment.username}:</strong>
                    ${data.data.comment.content}
                    <span class="comment-date">now</span>
                `;
                commentsList.insertBefore(comment, commentsList.firstChild);
                
                // Limpiar input
                input.value = '';
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});
</script>