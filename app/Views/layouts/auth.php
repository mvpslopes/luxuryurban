<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="dark">
    <meta name="theme-color" content="#000000">
    <title>Login — Luxury Urban</title>
    <link rel="icon" type="image/png" href="<?= asset('logo/logo_icone.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body class="auth-page">
    <?php require base_path('app/Views/partials/splash.php'); ?>

    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="<?= asset('logo/logo.png') ?>" alt="Luxury Urban" class="auth-logo__img">
            </div>
            <p class="auth-subtitle">Sistema de gestão</p>

            <?php if ($msg = flash('error')): ?>
                <div class="auth-alert"><?= e($msg) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>
</body>
</html>
