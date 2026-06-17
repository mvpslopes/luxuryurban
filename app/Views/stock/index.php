<?php
$title = 'Estoque';
if (\App\Core\Auth::can('stock.manage')) {
    $headerAction = '<a href="' . url('/estoque/movimentacao') . '" class="btn btn-primary">Movimentar</a> <a href="' . url('/estoque/historico') . '" class="btn btn-secondary">Histórico</a>';
}
?>
<div class="card">
    <div class="table-wrap">
    <table class="table">
        <thead><tr><th>SKU</th><th>Produto</th><th>Categoria</th><th>Quantidade</th><th>Mínimo</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= e($p['sku']) ?></td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category_name']) ?></td>
                <td>
                    <?php if ((int)$p['quantity'] <= (int)$p['min_stock']): ?>
                        <span class="badge badge-danger"><?= (int)$p['quantity'] ?></span>
                    <?php else: ?>
                        <?= (int)$p['quantity'] ?>
                    <?php endif; ?>
                </td>
                <td><?= (int)$p['min_stock'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
