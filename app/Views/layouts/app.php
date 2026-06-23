<?php

declare(strict_types=1);

use App\Core\Auth;

$currentUser = Auth::user();
$errors = $_SESSION['_errors'] ?? [];
unset($_SESSION['_errors']);
$old = $_SESSION['_old'] ?? [];
unset($_SESSION['_old']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="dark">
    <meta name="theme-color" content="#000000">
    <title><?= e($title ?? 'Dashboard') ?> | Luxury Urban</title>
    <link rel="icon" type="image/png" href="<?= asset('logo/logo_icone.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
    <script src="<?= asset('assets/js/luxury-progress.js') ?>"></script>
</head>
<body>
<?php require base_path('app/Views/partials/splash.php'); ?>
<div class="app-shell">
    <?php require base_path('app/Views/partials/sidebar.php'); ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-area">
        <?php require base_path('app/Views/partials/header.php'); ?>
        <main class="page-content">
            <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('error')): ?>
                <div class="alert alert-error"><?= e($msg) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script src="<?= asset('assets/js/app.js') ?>"></script>
<script>
(function () {
    var sidebar  = document.querySelector('.sidebar');
    var overlay  = document.getElementById('sidebarOverlay');
    var toggle   = document.getElementById('sidebarToggle');
    if (!sidebar || !overlay || !toggle) return;

    var mq = window.matchMedia('(max-width: 1024px)');

    function open() {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }
    function isDrawerMode() {
        return mq.matches;
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? close() : open();
    });
    overlay.addEventListener('click', close);

    sidebar.querySelectorAll('.nav-item').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isDrawerMode()) close();
        });
    });

    mq.addEventListener('change', function () {
        if (!isDrawerMode()) close();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) close();
    });
}());
</script>
<script>
// Fallback do sistema de modais: garante `openModal()` mesmo se `app.js` não carregar.
(function () {
    if (typeof window.openModal === 'function' && typeof window.closeModal === 'function') return;

    window.openModal = function (id) {
        var bd = document.getElementById(id);
        if (!bd) return;
        bd.classList.add('open');
        document.body.style.overflow = 'hidden';
        var first = bd.querySelector('input:not([type=hidden]),select,textarea');
        if (first) setTimeout(function () { first.focus(); }, 80);
    };

    window.closeModal = function (id) {
        var bd = document.getElementById(id);
        if (!bd) return;
        bd.classList.remove('open');
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.modal-backdrop.open').forEach(function (bd) {
            bd.classList.remove('open');
            document.body.style.overflow = '';
        });
    });
})();
</script>
</body>
</html>
