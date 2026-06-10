<div class="single-photo-page">

    <div class="single-photo-card">

        <!-- FOTO -->
        <img
            src="assets/images/uploads/<?= htmlspecialchars($photo['filename']) ?>"
            alt=""
            class="single-photo"
        >

        <!-- META -->
        <div class="photo-meta">

            <div class="photo-meta-row">

                <!-- USER -->
                <h3 class="photo-user">
                    <?= htmlspecialchars($photo['username']) ?>
                </h3>

                <!-- LIKES -->
                <span class="photo-likes">
                    ❤️ <?= $photo['likes_count'] ?>
                </span>

                <!-- LIKE BUTTON (solo si no eres el autor) -->
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

        <!-- COMMENTS -->
        <div class="comments">

            <h4>
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

        <!-- COMMENT FORM -->
        <?php if(isset($_SESSION['user_id']) && !$photo['is_owner']): ?>

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

        form.addEventListener('submit', async function (e) {

            e.preventDefault();

            const photoId = this.dataset.photoId;
            const input = this.querySelector('.comment-input');

            const response = await fetch('index.php?page=addComment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'photo_id=' + photoId + '&content=' + encodeURIComponent(input.value)
            });

            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success) {

                const commentsList = this.parentElement.querySelector('.comments');

                const div = document.createElement('div');
                div.className = 'comment';
                div.innerHTML =
                    '<strong>' + data.data.comment.username + '</strong> ' +
                    data.data.comment.content;

                commentsList.appendChild(div);

                input.value = '';
            }
        });
    });
</script>
