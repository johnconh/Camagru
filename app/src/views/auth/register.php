<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h2>Create an Account</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    ✅ <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input 
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input 
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input 
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        required
                    >
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <p class="auth-link">
                Already have an account? <a href="index.php?page=login">Login here</a>.
            </p>
        </div>
    </div>
</div>