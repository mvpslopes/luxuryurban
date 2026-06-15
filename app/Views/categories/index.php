<?php
$title = 'Categorias';
$headerAction = '<a href="' . url('/categorias/novo') . '" class="btn btn-primary">Nova categoria</a>';
?>
<div class="card">
    <table class="table">
        <thead><tr><th>Nome</th><th>Ordem</th><th>Produtos</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categories as $c): ?>
            <tr>
                <td><?= e($c['name']) ?></td>
                <td><?= (int) $c['sort_order'] ?></td>
                <td><?= (int) $c['products_count'] ?></td>
                <td><?= $c['active'] ? '<span class="badge badge-success">Ativa</span>' : '<span class="badge badge-neutral">Inativa</span>' ?></td>
                <td><a href="<?= url("/categorias/{$c['id']}/editar") ?>" class="btn btn-ghost btn-sm">Editar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
