<?php $title = 'Dashboard'; ?>
<div class="stats-grid">
    <div class="card stat-card">
        <span class="stat-label">Vendas hoje</span>
        <span class="stat-value"><?= (int) $salesToday ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Faturamento hoje</span>
        <span class="stat-value"><?= money((float) $revenueToday) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Faturamento mês</span>
        <span class="stat-value"><?= money((float) $revenueMonth) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label"><?= \App\Core\Auth::isAdmin() ? 'Clientes' : 'Clientes loja' ?></span>
        <span class="stat-value"><?= (int) $totalCustomers ?></span>
    </div>
</div>

<?php if (\App\Core\Auth::isAdmin() && (int) $pendingApprovals > 0): ?>
<div class="alert alert-warning">
    <?= (int) $pendingApprovals ?> venda(s) aguardando aprovação de desconto.
    <a href="<?= url('/aprovacoes') ?>">Ver aprovações</a>
</div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <h2 class="card-title">Vendas — últimos 30 dias</h2>
        <canvas id="salesChart" height="120"></canvas>
    </div>
    <?php if (\App\Core\Auth::isAdmin() && !empty($lowStock)): ?>
    <div class="card">
        <h2 class="card-title">Estoque baixo</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Produto</th><th>Qtd</th><th>Mín.</th></tr></thead>
                <tbody>
                <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td><?= e($p['name']) ?></td>
                        <td><span class="badge badge-danger"><?= (int) $p['quantity'] ?></span></td>
                        <td><?= (int) $p['min_stock'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
window.chartData = <?= json_encode(array_column($chartData, 'total', 'day')) ?>;
</script>
