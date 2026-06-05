<div class="single-photo-page">

    <div class="single-photo-card">

        <img
            src="assets/images/uploads/<?= htmlspecialchars($photo['filename']) ?>"
            alt=""
            class="single-photo"
        >

        <div class="photo-meta">

            <h3>
                <?= htmlspecialchars($photo['username']) ?>
            </h3>

            <p>
                ❤️ <?= $photo['likes_count'] ?>
            </p>

        </div>

        <?php if(
            isset($_SESSION['user_id'])
        ): ?>

            <button
                class="like-btn <?= $photo['liked_by_me'] ? 'liked' : '' ?>"
                data-photo-id="<?= $photo['id'] ?>"
            >

                <?= $photo['liked_by_me']
                    ? '❤️ Unlike'
                    : '🤍 Like' ?>

            </button>

        <?php endif; ?>

        <hr>

        <div class="comments">

            <h4>
                Comments
                (<?= count($photo['comments']) ?>)
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

        <?php if(isset($_SESSION['user_id'])): ?>

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