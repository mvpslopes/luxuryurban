<?php
$title = 'Produtos';
if (\App\Core\Auth::can('products.manage')) {
    $headerAction = '<a href="' . url('/produtos/novo') . '" class="btn btn-primary">Novo produto</a>';
}
?>
<div class="card mb-3">
    <form method="GET" class="filter-bar">
        <input type="text" name="q" class="input" placeholder="Buscar..." value="<?= e($search) ?>">
        <select name="category" class="input">
            <option value="">Todas categorias</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string)$categoryId === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filtrar</button>
    </form>
</div>
<div class="card">
    <table class="table">
        <thead><tr><th>SKU</th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= e($p['sku']) ?></td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= money((float)$p['price']) ?></td>
                <td>
                    <?php if ((int)$p['stock_qty'] <= (int)$p['min_stock']): ?>
                        <span class="badge badge-danger"><?= (int)$p['stock_qty'] ?></span>
                    <?php else: ?>
                        <?= (int)$p['stock_qty'] ?>
                    <?php endif; ?>
                </td>
                <td><?= $p['active'] ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-neutral">Inativo</span>' ?></td>
                <td>
                    <?php if (\App\Core\Auth::can('products.manage')): ?>
                        <a href="<?= url("/produtos/{$p['id']}/editar") ?>" class="btn btn-ghost btn-sm">Editar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
