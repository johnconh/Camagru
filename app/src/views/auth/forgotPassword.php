<div class= "auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h2>Reset Password</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Enter your email address and we'll send you a link to reset your password.
            </p>
            <div id="message-container"></div>
            <form method="POST" id="forgotPasswordForm">
                <div class="form-group">
                    <input 
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="Enter your email"
                    >
                </div>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Send Reset Link
                </button>
            </form>
        </div>
    </div>
</div>
<script>
const form = document.getElementById('forgotPasswordForm');
const messageContainer = document.getElementById('message-container');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', async function(event) {
    event.preventDefault();

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    const formData = new FormData(form);

    try{
        const response = await fetch('index.php?page=forgotPassword', {
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
        } else {
            showMessage(data.message, 'error');
        }
    } catch {
        showMessage('An error occurred. Please try again.', 'error');
    }finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Reset Link';
    }
});
</script>