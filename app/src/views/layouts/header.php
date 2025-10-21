<header class="main-header">
    <nav>
        <a href="?page=home">📸 Camagru</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="?page=logout">Logout</a>
        <?php else: ?>
            <a href="?page=login">Login</a>
            <a href="?page=register">Register</a>
        <?php endif; ?>
    </nav>
</header>