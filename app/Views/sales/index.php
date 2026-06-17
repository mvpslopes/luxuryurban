<?php
$title = 'Vendas';

$headerAction = '<a href="' . url('/vendas/nova') . '" class="btn btn-primary">Nova venda</a>';
?>

<div class="card">
    <div class="table-toolbar">
        <h2>Vendas</h2>
    </div>

    <form method="GET" class="table-filters">
        <div class="filters-left">
            <select name="status" class="input filter-select">
                <option value="">Todos os status</option>
                <option value="concluida" <?= $status === 'concluida' ? 'selected' : '' ?>>Concluída</option>
                <option value="pendente_aprovacao" <?= $status === 'pendente_aprovacao' ? 'selected' : '' ?>>Pendente</option>
                <option value="estornada" <?= $status === 'estornada' ? 'selected' : '' ?>>Estornada</option>
            </select>
        </div>
        <div class="filters-right">
            <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Recibo</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Total</th>
                <th>Pagamento</th>
                <th>Status</th>
                <th>Data</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td><?= e($s['receipt_number']) ?></td>
                    <td><?= e($s['customer_name']) ?></td>
                    <td><?= e($s['seller_name']) ?></td>
                    <td><?= money((float) $s['total']) ?></td>
                    <td><?= e($s['payment_method_name']) ?></td>
                    <td>
                        <span class="badge <?= sale_status_class($s['status']) ?>">
                            <?= sale_status_label($s['status']) ?>
                        </span>
                    </td>
                    <td><?= format_date($s['created_at']) ?></td>
                    <td>
                        <a href="<?= url("/vendas/{$s['id']}") ?>" class="btn btn-ghost btn-sm">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
