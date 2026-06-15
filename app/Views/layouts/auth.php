<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Luxury Urban</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-card card">
        <div class="auth-brand">
            <h1>Luxury Urban</h1>
            <p>Sistema de gestão</p>
        </div>
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-error"><?= e($msg) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </div>
</body>
</html>
