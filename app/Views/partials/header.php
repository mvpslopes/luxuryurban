<header class="page-header">
    <div>
        <p class="breadcrumb">Luxury Urban / <?= e($title ?? 'Dashboard') ?></p>
        <h1 class="page-title"><?= e($title ?? 'Dashboard') ?></h1>
    </div>
    <div class="header-actions">
        <?php if (isset($headerAction)): ?>
            <?= $headerAction ?>
        <?php endif; ?>
        <div class="user-chip">
            <span><?= e($currentUser['name'] ?? '') ?></span>
            <small><?= e(role_label($currentUser['role'] ?? '')) ?></small>
        </div>
    </div>
</header>
