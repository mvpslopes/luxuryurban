<header class="topbar">
    <button class="topbar-toggle" id="sidebarToggle" aria-label="Abrir menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <div class="topbar-search">
        <?= icon('search', 16) ?>
        <input type="text" placeholder="Buscar..." aria-label="Buscar">
    </div>

    <div class="topbar-actions">
        <?php if (isset($headerAction) && $headerAction): ?>
            <?= $headerAction ?>
        <?php endif; ?>
        <div class="user-chip">
            <span><?= e($currentUser['name'] ?? '') ?></span>
            <small><?= e(role_label($currentUser['role'] ?? '')) ?></small>
        </div>
    </div>
</header>
