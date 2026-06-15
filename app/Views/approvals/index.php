<?php $title = 'Aprovações de desconto'; ?>
<div class="card">
    <?php if (empty($pending)): ?>
        <p class="text-muted">Nenhuma aprovação pendente.</p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>Recibo</th><th>Cliente</th><th>Vendedor</th><th>Desconto</th><th>Total</th><th>Data</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($pending as $p): ?>
            <tr>
                <td><?= e($p['receipt_number']) ?></td>
                <td><?= e($p['customer_name']) ?></td>
                <td><?= e($p['requested_by_name']) ?></td>
                <td><span class="badge badge-warning"><?= number_format((float)$p['discount_percent'], 1) ?>%</span></td>
                <td><?= money((float)$p['total']) ?></td>
                <td><?= format_date($p['created_at']) ?></td>
                <td class="actions-cell">
                    <form method="POST" action="<?= url("/aprovacoes/{$p['id']}/aprovar") ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary btn-sm">Aprovar</button>
                    </form>
                    <form method="POST" action="<?= url("/aprovacoes/{$p['id']}/rejeitar") ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Rejeitar</button>
                    </form>
                    <a href="<?= url("/vendas/{$p['sale_id']}") ?>" class="btn btn-ghost btn-sm">Ver</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
