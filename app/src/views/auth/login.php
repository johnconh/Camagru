<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h2>Login to Your Account</h2>

            <div id="message-container"></div>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input 
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
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
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Login
                </button>
            </form>
            
            <p class="auth-link">
                Don't have an account? <a href="index.php?page=register">Register here</a>.
            </p>
            <p class="auth-link">
                <a href="index.php?page=forgotPassword">Forgot your password?</a>
            </p>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('loginForm');
const submitBtn = document.getElementById('submitBtn');
const messageContainer = document.getElementById('message-container');

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
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';
    
    const formData = new FormData(form);
    
    try {
        const response = await fetch('index.php?page=login', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.data.redirect || 'index.php?page=home';
            }, 1000);
        } else {
            showMessage(data.message, 'error');
        }
        
    } catch (error) {
        showMessage('An error occurred. Please try again.', 'error');
        console.error('Error:', error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    }
});
</script>