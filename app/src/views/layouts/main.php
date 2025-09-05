<?php require_once 'header.php'; ?>

<main>
    <?php
    if (isset($view)) {
        require_once $view;
    }
    ?>
</main>

<?php require_once 'footer.php'; ?>
