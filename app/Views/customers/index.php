<?php
$title = 'Clientes';
$headerAction = '<a href="' . url('/clientes/novo') . '" class="btn btn-primary">Novo cliente</a>';
?>
<div class="card mb-3">
    <form method="GET" class="filter-bar">
        <input type="text" name="q" class="input" placeholder="Nome, CPF ou telefone..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
    </form>
</div>
<div class="card">
    <table class="table">
        <thead><tr><th>Nome</th><th>Documento</th><th>Telefone</th><th>Vendas</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td><?= e($c['name']) ?></td>
                <td><?= e($c['document'] ?? '-') ?></td>
                <td><?= e($c['phone'] ?? '-') ?></td>
                <td><?= (int)$c['sales_count'] ?></td>
                <td><a href="<?= url("/clientes/{$c['id']}/editar") ?>" class="btn btn-ghost btn-sm">Editar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
