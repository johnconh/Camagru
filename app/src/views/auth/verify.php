<div class="auth-page">

    <div class="auth-container">

        <div class="auth-card verify-card">

            <?php if(isset($success) && $success): ?>

                <div class="verify-icon success">
                    ✅
                </div>

                <h1>Verified!</h1>

                <p class="verify-text">
                    <?= htmlspecialchars($message) ?>
                </p>

                <a
                    href="index.php?page=login"
                    class="btn btn-primary"
                >
                    Login now
                </a>

            <?php else: ?>

                <div class="verify-icon error">
                    ❌
                </div>

                <h1>Verification failed</h1>

                <p class="verify-text">
                    <?= htmlspecialchars($message) ?>
                </p>

                <a
                    href="index.php?page=register"
                    class="btn btn-secondary"
                >
                    Create account
                </a>

            <?php endif; ?>

        </div>

    </div>

</div>