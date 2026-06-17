<?php
$title = 'Produtos';
$hasErrors = !empty($_SESSION['_errors'] ?? []);
$canManage = \App\Core\Auth::can('products.manage');

if ($canManage) {
    $headerAction = '<button type="button" class="btn btn-primary" onclick="openProductModal()">Novo produto</button>';
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
    <div class="table-toolbar">
        <h2>Produtos</h2>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th></th>
                </tr>
            </thead>
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
                        <?php if ($canManage): ?>
                            <a href="<?= url("/produtos/{$p['id']}/editar") ?>" class="btn btn-ghost btn-sm">Editar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canManage): ?>
<!-- Modal Novo Produto -->
<div class="modal-backdrop" id="modal-produto">
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Novo produto</span>
            <button type="button" class="modal-close" onclick="closeModal('modal-produto')" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <form method="POST" id="form-produto" action="<?= url('/produtos') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="prod-name">Nome *</label>
                        <input type="text" id="prod-name" name="name" class="input"
                               value="<?= e(old('name')) ?>" required>
                        <?php if (!empty($_SESSION['_errors']['name'] ?? '')): ?>
                            <span class="error"><?= e($_SESSION['_errors']['name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="prod-sku">SKU</label>
                        <input type="text" id="prod-sku" name="sku" class="input"
                               value="<?= e(old('sku')) ?>" placeholder="Auto-gerado se vazio">
                    </div>
                    <div class="form-group">
                        <label for="prod-category">Categoria *</label>
                        <?php
                        $categorySelectId = 'prod-category';
                        $selectedCategoryId = old('category_id');
                        require base_path('app/Views/partials/category_quick_add.php');
                        ?>
                        <?php if (!empty($_SESSION['_errors']['category_id'] ?? '')): ?>
                            <span class="error"><?= e($_SESSION['_errors']['category_id']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="prod-price">Preço de venda *</label>
                        <input type="text" id="prod-price" name="price" class="input"
                               value="<?= e(old('price')) ?>" required placeholder="0.00">
                        <?php if (!empty($_SESSION['_errors']['price'] ?? '')): ?>
                            <span class="error"><?= e($_SESSION['_errors']['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="prod-cost">Preço de custo</label>
                        <input type="text" id="prod-cost" name="cost_price" class="input"
                               value="<?= e(old('cost_price')) ?>" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="prod-min-stock">Estoque mínimo</label>
                        <input type="number" id="prod-min-stock" name="min_stock" class="input"
                               value="<?= e(old('min_stock', '5')) ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="prod-initial-stock">Estoque inicial</label>
                        <input type="number" id="prod-initial-stock" name="initial_stock" class="input"
                               value="<?= e(old('initial_stock', '0')) ?>" min="0">
                    </div>
                    <div class="form-group full-width">
                        <label for="prod-description">Descrição</label>
                        <textarea id="prod-description" name="description" class="input" rows="3"><?= e(old('description')) ?></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="prod-photos">Fotos (máx. 5, JPG/PNG/WebP)</label>
                        <input type="file" id="prod-photos" name="photos[]" class="input"
                               accept="image/jpeg,image/png,image/webp" multiple>
                    </div>
                    <div class="form-group form-check">
                        <label>
                            <input type="checkbox" id="prod-active" name="active" checked>
                            Ativo
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-produto')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openProductModal() {
    var form = document.getElementById('form-produto');
    if (form) {
        form.reset();
        form.action = <?= json_encode(url('/produtos')) ?>;
        var active = document.getElementById('prod-active');
        if (active) active.checked = true;
        var minStock = document.getElementById('prod-min-stock');
        if (minStock) minStock.value = '5';
        var initialStock = document.getElementById('prod-initial-stock');
        if (initialStock) initialStock.value = '0';
    }
    openModal('modal-produto');
}
</script>

<?php if ($hasErrors): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modal-produto'); });</script>
<?php endif; ?>
<?php endif; ?>
