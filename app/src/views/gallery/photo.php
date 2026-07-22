<div class="single-photo-page">

    <div class="single-photo-card">

        <img
            src="assets/images/uploads/<?= htmlspecialchars($photo['filename']) ?>"
            alt=""
            class="single-photo"
        >

        <div class="photo-meta">

            <div class="photo-meta-row">

                <h3 class="photo-user">
                    <?= htmlspecialchars($photo['username']) ?>
                </h3>

                <span class="photo-likes">
                    ❤️ <?= $photo['likes_count'] ?>
                </span>

                <?php if(isset($_SESSION['user_id']) && !$photo['is_owner']): ?>

                    <button
                        class="like-btn <?= $photo['liked_by_me'] ? 'liked' : '' ?>"
                        data-photo-id="<?= $photo['id'] ?>"
                    >
                        <?= $photo['liked_by_me']
                            ? '❤️ Unlike'
                            : '🤍 Like' ?>
                    </button>

                <?php endif; ?>

            </div>

        </div>

        <hr>

        <div class="comments">

            <h4 class="comments-count">
                Comments (<?= count($photo['comments']) ?>)
            </h4>

            <?php foreach($photo['comments'] as $comment): ?>

                <div class="comment">

                    <strong>
                        <?= htmlspecialchars($comment['username']) ?>
                    </strong>

                    <?= htmlspecialchars($comment['content']) ?>

                </div>

            <?php endforeach; ?>

        </div>

        <?php if (isset($_SESSION['user_id']) && !$photo['is_owner'] && !Comment::existsByUserAndPhoto($_SESSION['user_id'], $photo['id'])): ?>

            <form
                class="comment-form"
                data-photo-id="<?= $photo['id'] ?>"
            >

                <input
                    type="text"
                    class="comment-input"
                    placeholder="Add a comment..."
                    required
                >

                <button type="submit">
                    Post
                </button>

            </form>

        <?php endif; ?>

    </div>

</div>

<script>
    document.querySelectorAll('.like-btn').forEach(btn => {

        btn.addEventListener('click', async function () {

            const photoId = this.dataset.photoId;

            const response = await fetch('index.php?page=toggleLike', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'photo_id=' + photoId
            });

            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success) {

                const likesEl = this.closest('.single-photo-card')
                    .querySelector('.photo-likes');

                likesEl.textContent = '❤️ ' + data.data.likes_count;

                if (data.data.action === 'added') {
                    this.classList.add('liked');
                    this.innerHTML = '❤️ Unlike';
                } else {
                    this.classList.remove('liked');
                    this.innerHTML = '🤍 Like';
                }
            }
        });
    });

    document.querySelectorAll('.comment-form').forEach(form => {

        let isSubmitting = false;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (isSubmitting) return;
            isSubmitting = true;

            const photoId = this.dataset.photoId;
            const input = this.querySelector('.comment-input');
            const button = this.querySelector('button');

            button.disabled = true;

            try {
                const response = await fetch('index.php?page=addComment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        photo_id: photoId,
                        content: input.value
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    console.warn(data.message);
                    return;
                }

                const root = document.querySelector(
                    `.single-photo-card[data-photo-id="${photoId}"]`
                ) || document.querySelector('.single-photo-card');

                const commentsList = root.querySelector('.comments');

                const div = document.createElement('div');
                div.className = 'comment';
                div.innerHTML = `
                    <strong>${data.data.comment.username}</strong>
                    ${data.data.comment.content}
                `;

                commentsList.appendChild(div);

                root.querySelector('.comments-count').textContent =
                    `Comments (${data.data.comments_count})`;

                input.value = '';

                form.remove();

            } catch (err) {
                console.error('Error:', err);
            } finally {
                isSubmitting = false;
                button.disabled = false;
            }
        });
    });
</script>
