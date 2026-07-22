<div class="gallery-container">

    <?php if(empty($photos)): ?>

        <p>No photos yet</p>

    <?php else: ?>

        <div class="gallery-grid">

            <?php foreach($photos as $photo): ?>

                <div class="gallery-card">

                    <a href="index.php?page=photo&id=<?= $photo['id'] ?>">

                        <img
                            src="assets/images/uploads/<?= htmlspecialchars($photo['filename']) ?>"
                            alt="Photo"
                        >

                    </a>

                    <div class="gallery-info">

                        <span>
                            👤 <?= htmlspecialchars($photo['username']) ?>
                        </span>

                        <span>
                            ❤️ <?= $photo['likes_count'] ?>
                        </span>

                        <span>
                            💬 <?= count($photo['comments']) ?>
                        </span>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <?php if($totalPages > 1): ?>

        <div class="pagination">

            <?php if($page > 1): ?>

                <a href="?page=gallery&p=<?= $page - 1 ?>">
                    ← Previous
                </a>

            <?php endif; ?>

            <span>
                Page <?= $page ?> of <?= $totalPages ?>
            </span>

            <?php if($page < $totalPages): ?>

                <a href="?page=gallery&p=<?= $page + 1 ?>">
                    Next →
                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>
    
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