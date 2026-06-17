<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">LU</span>
        <span class="brand-text">Luxury Urban</span>
    </div>
    <nav class="sidebar-nav">
        <?php if (\App\Core\Auth::isVendedor()): ?>
            <a href="<?= url('/clientes') ?>" class="nav-item <?= is_active_nav('/clientes') ?>">
                <span class="nav-icon"><?= icon('user-group') ?></span>
                <span class="nav-label">Clientes</span>
            </a>
            <a href="<?= url('/vendas/nova') ?>" class="nav-item <?= is_active_nav('/vendas/nova') ?>">
                <span class="nav-icon"><?= icon('shopping-cart') ?></span>
                <span class="nav-label">Nova Venda</span>
            </a>
            <a href="<?= url('/vendas') ?>" class="nav-item <?= is_active_nav('/vendas') ?>">
                <span class="nav-icon"><?= icon('receipt') ?></span>
                <span class="nav-label">Vendas</span>
            </a>
        <?php else: ?>
            <a href="<?= url('/dashboard') ?>" class="nav-item <?= is_active_nav('/dashboard') ?>">
                <span class="nav-icon"><?= icon('dashboard') ?></span>
                <span class="nav-label">Dashboard</span>
            </a>
            <?php if (\App\Core\Auth::isRoot()): ?>
            <a href="<?= url('/usuarios') ?>" class="nav-item <?= is_active_nav('/usuarios') ?>">
                <span class="nav-icon"><?= icon('users') ?></span>
                <span class="nav-label">Usuários</span>
            </a>
            <?php endif; ?>
            <a href="<?= url('/produtos') ?>" class="nav-item <?= is_active_nav('/produtos') ?>">
                <span class="nav-icon"><?= icon('package') ?></span>
                <span class="nav-label">Produtos</span>
            </a>
            <a href="<?= url('/categorias') ?>" class="nav-item <?= is_active_nav('/categorias') ?>">
                <span class="nav-icon"><?= icon('tag') ?></span>
                <span class="nav-label">Categorias</span>
            </a>
            <a href="<?= url('/formas-pagamento') ?>" class="nav-item <?= is_active_nav('/formas-pagamento') ?>">
                <span class="nav-icon"><?= icon('credit-card') ?></span>
                <span class="nav-label">Pagamentos</span>
            </a>
            <a href="<?= url('/estoque') ?>" class="nav-item <?= is_active_nav('/estoque') ?>">
                <span class="nav-icon"><?= icon('warehouse') ?></span>
                <span class="nav-label">Estoque</span>
            </a>
            <a href="<?= url('/aprovacoes') ?>" class="nav-item <?= is_active_nav('/aprovacoes') ?>">
                <span class="nav-icon"><?= icon('check-circle') ?></span>
                <span class="nav-label">Aprovações</span>
            </a>
            <a href="<?= url('/clientes') ?>" class="nav-item <?= is_active_nav('/clientes') ?>">
                <span class="nav-icon"><?= icon('user-group') ?></span>
                <span class="nav-label">Clientes</span>
            </a>
            <a href="<?= url('/vendas/nova') ?>" class="nav-item <?= is_active_nav('/vendas/nova') ?>">
                <span class="nav-icon"><?= icon('shopping-cart') ?></span>
                <span class="nav-label">Nova Venda</span>
            </a>
            <a href="<?= url('/vendas') ?>" class="nav-item <?= is_active_nav('/vendas') ?>">
                <span class="nav-icon"><?= icon('receipt') ?></span>
                <span class="nav-label">Vendas</span>
            </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= url('/logout') ?>" class="nav-item">
            <span class="nav-icon"><?= icon('log-out') ?></span>
            <span class="nav-label">Sair</span>
        </a>
    </div>
</aside>
