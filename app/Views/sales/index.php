<?php
$title = 'Vendas';
$headerAction = \App\Core\Auth::can('sales.create') ? '<a href="' . url('/vendas/nova') . '" class="btn btn-primary">Nova venda</a>' : '';
?>
<div class="card mb-3">
    <form method="GET" class="filter-bar">
        <select name="status" class="input">
            <option value="">Todos status</option>
            <option value="concluida" <?= $status === 'concluida' ? 'selected' : '' ?>>Concluída</option>
            <option value="pendente_aprovacao" <?= $status === 'pendente_aprovacao' ? 'selected' : '' ?>>Pendente</option>
            <option value="estornada" <?= $status === 'estornada' ? 'selected' : '' ?>>Estornada</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filtrar</button>
    </form>
</div>
<div class="card">
    <table class="table">
        <thead><tr><th>Recibo</th><th>Cliente</th><th>Vendedor</th><th>Pagamento</th><th>Total</th><th>Status</th><th>Data</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($sales as $s): ?>
            <tr>
                <td><?= e($s['receipt_number']) ?></td>
                <td><?= e($s['customer_name']) ?></td>
                <td><?= e($s['seller_name']) ?></td>
                <td><?= e($s['payment_method_name']) ?></td>
                <td><?= money((float)$s['total']) ?></td>
                <td><span class="badge <?= sale_status_class($s['status']) ?>"><?= sale_status_label($s['status']) ?></span></td>
                <td><?= format_date($s['created_at']) ?></td>
                <td><a href="<?= url("/vendas/{$s['id']}") ?>" class="btn btn-ghost btn-sm">Ver</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
