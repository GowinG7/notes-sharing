<?php require __DIR__ . '/includes/helpers.php';
unset($_SESSION['user_id']);
redirect(base_url() . '/');
