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

            <form method="POST" action="index.php?page=register" id="registerForm">
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
                    <div class="input-with-toggle">
                    <label for="password">Password:</label>
                        <input 
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="password" aria-label="Show password">👁️</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-with-toggle">
                    <label for="confirm">Confirm Password:</label>
                        <input
                            type="password"
                            id="confirm"
                            name="confirm"
                            placeholder="Confirm your password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="confirm" aria-label="Show password">👁️</button>
                    </div>
                    <div id="password-requirements" class="password-requirements">
                        <p class="requirement" id="req-length">✗ Minimum 8 characters</p>
                        <p class="requirement" id="req-uppercase">✗ At least one uppercase letter</p>
                        <p class="requirement" id="req-lowercase">✗ At least one lowercase letter</p>
                        <p class="requirement" id="req-number">✗ At least one number</p>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <p class="auth-link">
                Already have an account? <a href="index.php?page=login">Login here</a>.
            </p>
        </div>
    </div>
</div>
<script>
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const form = document.getElementById('registerForm');

passwordInput.addEventListener('input', function() {
    const password = this.value;

    checkRequirement('req-length', password.length >= 8);

    checkRequirement('req-uppercase', /[A-Z]/.test(password));

    checkRequirement('req-lowercase', /[a-z]/.test(password));

    checkRequirement('req-number', /[0-9]/.test(password));
});

function checkRequirement(id, isValid) {
    const element = document.getElementById(id);
    if (isValid) {
        element.classList.add('valid');
        element.textContent = element.textContent.replace('✗', '✓');
    } else {
        element.classList.remove('valid');
        element.textContent = element.textContent.replace('✓', '✗');
    }
}

form.addEventListener('submit', function(e) {
    const password = passwordInput.value;
    
    if (password.length < 8 || 
        !/[A-Z]/.test(password) || 
        !/[a-z]/.test(password) || 
        !/[0-9]/.test(password)) {
        e.preventDefault();
        alert('The password does not meet the complexity requirements.');
        return false;
    }
    
    if (password !== confirmInput.value) {
        e.preventDefault();
        alert('The passwords do not match.');
        return false;
    }
});

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetId = btn.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.setAttribute('aria-label', 'Hide password');
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.setAttribute('aria-label', 'Show password');
            btn.textContent = '👁️';
        }
    });
});
</script>