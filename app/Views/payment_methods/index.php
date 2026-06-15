<?php
$title = 'Formas de pagamento';
$headerAction = '<a href="' . url('/formas-pagamento/novo') . '" class="btn btn-primary">Nova forma</a>';
?>
<div class="card">
    <table class="table">
        <thead><tr><th>Nome</th><th>Ordem</th><th>Vendas</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($methods as $m): ?>
            <tr>
                <td><?= e($m['name']) ?></td>
                <td><?= (int) $m['sort_order'] ?></td>
                <td><?= (int) $m['sales_count'] ?></td>
                <td><?= $m['active'] ? '<span class="badge badge-success">Ativa</span>' : '<span class="badge badge-neutral">Inativa</span>' ?></td>
                <td><a href="<?= url("/formas-pagamento/{$m['id']}/editar") ?>" class="btn btn-ghost btn-sm">Editar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
