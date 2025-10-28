<?php
$token = $_GET['token'] ?? '';
$tokenValid = false;

if (!empty($token)) {
    $tokenValid = User::verifyResetToken($token) !== false;
}
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h2>Set New Password</h2>

            <?php if (empty($token) || !$tokenValid): ?>
                <div class="alert alert-error">
                    ❌ Invalid or expired reset token.
                </div>
                <p class="auth-link" style="text-align: center;">
                    <a href="index.php?page=forgotPassword">Request a new reset link</a>
                </p>
            <?php else: ?>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Enter your new password below.
                </p>

                <div id="message-container"></div>

                <form method="POST" id="resetPasswordForm">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                    
                    <div class="form-group">
                        <div class="input-with-toggle">
                            <label for="password">New Password:</label>
                            <input 
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your new password"
                                required
                            >
                            <button type="button" class="toggle-password" data-target="password" aria-label="Show password">👁️</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-with-toggle">
                            <label for="confirm">Confirm New Password:</label>
                            <input
                                type="password"
                                id="confirm"
                                name="confirm"
                                placeholder="Confirm your new password"
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
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm');
const form = document.getElementById('resetPasswordForm');
const submitBtn = document.getElementById('submitBtn');
const messageContainer = document.getElementById('message-container');

if (passwordInput) {
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

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (password.length < 8 || 
            !/[A-Z]/.test(password) || 
            !/[a-z]/.test(password) || 
            !/[0-9]/.test(password)) {
            showMessage('The password does not meet the complexity requirements.', 'error');
            return false;
        }
        
        if (password !== confirm) {
            showMessage('The passwords do not match.', 'error');
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Resetting...';
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('index.php?page=reset-password', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage(data.message, 'success');
                form.reset();
                setTimeout(() => {
                    window.location.href = 'index.php?page=login';
                }, 2000);
            } else {
                showMessage(data.message, 'error');
            }
            
        } catch (error) {
            showMessage('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Reset Password';
        }
    });
}
</script>