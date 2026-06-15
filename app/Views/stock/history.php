<?php $title = 'Histórico de estoque'; ?>
<div class="card">
    <table class="table">
        <thead><tr><th>Data</th><th>Produto</th><th>Tipo</th><th>Qtd</th><th>Saldo</th><th>Usuário</th><th>Obs.</th></tr></thead>
        <tbody>
        <?php foreach ($movements as $m): ?>
            <tr>
                <td><?= format_date($m['created_at']) ?></td>
                <td><?= e($m['product_name']) ?></td>
                <td><span class="badge badge-info"><?= e($m['type']) ?></span></td>
                <td><?= (int)$m['quantity'] ?></td>
                <td><?= (int)$m['balance_after'] ?></td>
                <td><?= e($m['user_name']) ?></td>
                <td><?= e($m['notes'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
