<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$username = null;
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__.'/../../models/user.php';
    $user = User::findByID($_SESSION['user_id']);
    if ($user && !empty($user['username'])) {
        $username = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
    }
}
?>

<header class="main-header">
    <a href="?page=home" class="logo" aria-label="Home">📸 Camagru</a>
    <nav>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <span>Welcome, <?php echo $username; ?>!</span>
            <a href="?page=logout" title="Logout">👋</a>
        <?php else: ?>
            <a href="?page=login">Login</a>
            <a href="?page=register">Register</a>
        <?php endif; ?>
    </nav>
</header>