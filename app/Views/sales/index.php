<?php
$title = 'Orders';

$headerAction = '';
$headerAction .= '<a href="' . url('/dashboard') . '" class="btn btn-ghost btn-sm">Order Statistics</a> ';
$headerAction .= '<a href="' . url('/clientes/novo') . '" class="btn btn-primary">New Customer</a>';
?>

<div class="card">
    <div class="table-toolbar">
        <h2>Orders</h2>
        <div class="toolbar-actions">
            <a href="<?= url('/dashboard') ?>" class="btn btn-ghost btn-sm">Help</a>
        </div>
    </div>

    <form method="GET" class="table-filters">
        <div class="filters-left">
            <select name="status" class="input" style="max-width: 280px;">
                <option value="">All status</option>
                <option value="concluida" <?= $status === 'concluida' ? 'selected' : '' ?>>Paid</option>
                <option value="pendente_aprovacao" <?= $status === 'pendente_aprovacao' ? 'selected' : '' ?>>Processing</option>
                <option value="estornada" <?= $status === 'estornada' ? 'selected' : '' ?>>Unpaid</option>
            </select>
        </div>
        <div class="filters-right">
            <input type="text" class="input" placeholder="Search..." style="max-width: 240px;">
            <button type="button" class="btn btn-secondary btn-sm" disabled>Export table</button>
            <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th style="width: 48px;"><input type="checkbox" class="table-checkbox" aria-label="Selecionar tudo"></th>
                <th>ID</th>
                <th>Reference</th>
                <th>New customer?</th>
                <th>Price</th>
                <th>Payment</th>
                <th>Status</th>
                <th style="width: 48px;"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td><input type="checkbox" class="table-checkbox" aria-label="Selecionar"></td>
                    <td><?= e($s['receipt_number']) ?></td>
                    <td><?= e($s['receipt_number']) ?></td>
                    <td><?= e($s['new_customer'] ?? 'No') ?></td>
                    <td><?= money((float) $s['total']) ?></td>
                    <td><?= e($s['payment_method_name']) ?></td>
                    <td>
                        <span class="badge <?= sale_status_class($s['status']) ?>">
                            <?= sale_status_label($s['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= url("/vendas/{$s['id']}") ?>" class="btn btn-ghost btn-sm btn-icon" aria-label="Options">
                            <?= icon('more-vertical', 20) ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
