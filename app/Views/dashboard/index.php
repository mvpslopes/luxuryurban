<?php
$title = 'Dashboard';
$isAdmin = \App\Core\Auth::isAdmin();
?>
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
        <span class="stat-label">Ticket médio hoje</span>
        <span class="stat-value"><?= money((float) $avgTicketToday) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Vendas no mês</span>
        <span class="stat-value"><?= (int) $salesMonth ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Faturamento mês</span>
        <span class="stat-value"><?= money((float) $revenueMonth) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Ticket médio mês</span>
        <span class="stat-value"><?= money((float) $avgTicketMonth) ?></span>
    </div>
</div>

<div class="stats-grid stats-grid--secondary">
    <div class="card stat-card">
        <span class="stat-label">Clientes cadastrados</span>
        <span class="stat-value"><?= (int) $totalCustomers ?></span>
    </div>
    <?php if ($isAdmin): ?>
    <div class="card stat-card">
        <span class="stat-label">Produtos ativos</span>
        <span class="stat-value"><?= (int) $totalProducts ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Aprovações pendentes</span>
        <span class="stat-value"><?= (int) $pendingApprovals ?></span>
    </div>
  <?php else: ?>
    <div class="card stat-card">
        <span class="stat-label">Minhas vendas pendentes</span>
        <span class="stat-value"><?= (int) $pendingApprovals ?></span>
    </div>
    <?php endif; ?>
</div>

<?php if ($isAdmin && (int) $pendingApprovals > 0): ?>
<div class="alert alert-warning">
    <?= (int) $pendingApprovals ?> venda(s) aguardando aprovação de desconto.
    <a href="<?= url('/aprovacoes') ?>">Ver aprovações</a>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="table-toolbar">
        <h2 class="card-title mb-0">Vendas recentes</h2>
        <a href="<?= url('/vendas') ?>" class="btn btn-ghost btn-sm">Ver todas</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Recibo</th>
                    <th>Cliente</th>
                    <?php if ($isAdmin): ?><th>Vendedor</th><?php endif; ?>
                    <th>Pagamento</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($recentSales)): ?>
                <tr>
                    <td colspan="<?= $isAdmin ? 8 : 7 ?>" class="text-muted" style="text-align:center;padding:24px;">
                        Nenhuma venda registrada ainda.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($recentSales as $sale): ?>
                    <tr>
                        <td><?= e($sale['receipt_number']) ?></td>
                        <td><?= e($sale['customer_name']) ?></td>
                        <?php if ($isAdmin): ?>
                            <td><?= e($sale['seller_name']) ?></td>
                        <?php endif; ?>
                        <td><?= e($sale['payment_method_name']) ?></td>
                        <td><?= money((float) $sale['total']) ?></td>
                        <td>
                            <span class="badge <?= sale_status_class($sale['status']) ?>">
                                <?= sale_status_label($sale['status']) ?>
                            </span>
                        </td>
                        <td><?= format_date($sale['created_at']) ?></td>
                        <td>
                            <a href="<?= url("/vendas/{$sale['id']}") ?>" class="btn btn-ghost btn-sm">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h2 class="card-title">Faturamento — últimos 30 dias</h2>
        <canvas id="salesChart" height="120"></canvas>
    </div>

    <?php if ($isAdmin && !empty($topSellers)): ?>
    <div class="card">
        <h2 class="card-title">Top vendedores do mês</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th>Vendas</th>
                        <th>Faturamento</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($topSellers as $seller): ?>
                    <tr>
                        <td><?= e($seller['seller_name']) ?></td>
                        <td><?= (int) $seller['sales_count'] ?></td>
                        <td><?= money((float) $seller['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php elseif ($isAdmin && !empty($lowStock)): ?>
    <div class="card">
        <h2 class="card-title">Estoque baixo</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Produto</th><th>SKU</th><th>Qtd</th><th>Mín.</th></tr></thead>
                <tbody>
                <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td><?= e($p['name']) ?></td>
                        <td><?= e($p['sku'] ?? '-') ?></td>
                        <td><span class="badge badge-danger"><?= (int) $p['quantity'] ?></span></td>
                        <td><?= (int) $p['min_stock'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="<?= url('/estoque') ?>" class="btn btn-ghost btn-sm mt-2">Ver estoque</a>
    </div>
    <?php endif; ?>
</div>

<?php if ($isAdmin && !empty($lowStock) && !empty($topSellers)): ?>
<div class="card mt-3">
    <h2 class="card-title">Estoque baixo</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Produto</th><th>SKU</th><th>Qtd</th><th>Mín.</th></tr></thead>
            <tbody>
            <?php foreach ($lowStock as $p): ?>
                <tr>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['sku'] ?? '-') ?></td>
                    <td><span class="badge badge-danger"><?= (int) $p['quantity'] ?></span></td>
                    <td><?= (int) $p['min_stock'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="<?= url('/estoque') ?>" class="btn btn-ghost btn-sm mt-2">Ver estoque</a>
</div>
<?php endif; ?>

<script>
window.chartData = <?= json_encode(array_column($chartData, 'total', 'day')) ?>;
</script>
