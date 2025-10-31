<div class="profile-container">
    <div class="profile-card">
        <h2>Edit Profile</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Update your account information
        </p>
        <div id="message-container"></div>
        <form id="editProfileForm" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input 
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?= htmlspecialchars($user['username'] ?? '') ?>"
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
                    value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                    required
                >
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

            <h3 style="margin-bottom: 15px;">Change Password (optional)</h3>
            <p style="color: #666; font-size: 0.9em; margin-bottom: 20px;">
                Leave blank if you don't want to change your password
            </p>

            <div class="form-group">
                <div class="input-with-toggle">
                    <label for="current_password">Current Password:</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        placeholder="Enter your current password"
                    >
                    <button type="button" class="toggle-password" data-target="current_password" aria-label="Show password">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <div class="input-with-toggle">
                    <label for="new_password">New Password:</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        placeholder="Enter your new password"
                    >
                    <button type="button" class="toggle-password" data-target="new_password" aria-label="Show password">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <div class="input-with-toggle">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your new password"
                    >
                    <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Show password">👁️</button>
                </div>
                <div id="password-requirements" class="password-requirements" style="display: none;">
                    <p class="requirement" id="req-length">✗ Minimum 8 characters</p>
                    <p class="requirement" id="req-uppercase">✗ At least one uppercase letter</p>
                    <p class="requirement" id="req-lowercase">✗ At least one lowercase letter</p>
                    <p class="requirement" id="req-number">✗ At least one number</p>
                </div>
            </div>

            <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

            <div class="form-group">
                <label class="checkbox-label">
                    <input 
                        type="checkbox"
                        id="email_notifications"
                        name="email_notifications"
                        <?= ($user['email_notifications'] ?? 1) ? 'checked' : '' ?>
                    >
                    <span>Receive email notifications when someone comments on my photos</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Save Changes
                </button>
                <a href="index.php?page=home" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const newPasswordInput = document.getElementById('new_password');
const confirmPasswordInput = document.getElementById('confirm_password');
const currentPasswordInput = document.getElementById('current_password');
const form = document.getElementById('editProfileForm');
const submitBtn = document.getElementById('submitBtn');
const messageContainer = document.getElementById('message-container');
const requirementsDiv = document.getElementById('password-requirements');

newPasswordInput.addEventListener('focus', function() {
    if (this.value.length > 0 || currentPasswordInput.value.length > 0) {
        requirementsDiv.style.display = 'block';
    }
});

newPasswordInput.addEventListener('input', function() {
    requirementsDiv.style.display = 'block';
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
    
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const currentPassword = currentPasswordInput.value;
    
    if (newPassword || confirmPassword || currentPassword) {
        if (!currentPassword) {
            showMessage('Current password is required to change password.', 'error');
            return false;
        }
        
        if (newPassword.length < 8 || 
            !/[A-Z]/.test(newPassword) || 
            !/[a-z]/.test(newPassword) || 
            !/[0-9]/.test(newPassword)) {
            showMessage('The new password does not meet the complexity requirements.', 'error');
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            showMessage('The new passwords do not match.', 'error');
            return false;
        }
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('index.php?page=editProfile', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message, 'success');
            currentPasswordInput.value = '';
            newPasswordInput.value = '';
            confirmPasswordInput.value = '';
            requirementsDiv.style.display = 'none';
        } else {
            showMessage(data.message, 'error');
        }
        
    } catch (error) {
        showMessage('An error occurred. Please try again.', 'error');
        console.error('Error:', error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Changes';
    }
});
</script>
