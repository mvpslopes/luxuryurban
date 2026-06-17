<form method="POST" action="<?= url($product ? "/produtos/{$product['id']}" : '/produtos') ?>" enctype="multipart/form-data" class="card form-card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="form-group">
            <label>Nome *</label>
            <input type="text" name="name" class="input" value="<?= e(old('name', $product['name'] ?? '')) ?>" required>
        </div>
        <div class="form-group">
            <label>SKU</label>
            <input type="text" name="sku" class="input" value="<?= e(old('sku', $product['sku'] ?? '')) ?>" <?= $product ? '' : 'placeholder="Auto-gerado se vazio"' ?>>
        </div>
        <div class="form-group">
            <label>Categoria *</label>
            <?php
            $categorySelectId = 'product-category';
            $modalId = 'modal-categoria-produto-form';
            $selectedCategoryId = old('category_id', $product['category_id'] ?? '');
            require base_path('app/Views/partials/category_quick_add.php');
            ?>
        </div>
        <div class="form-group">
            <label>Preço de venda *</label>
            <input type="text" name="price" class="input" value="<?= e(old('price', isset($product['price']) ? number_format((float)$product['price'], 2, '.', '') : '')) ?>" required>
        </div>
        <div class="form-group">
            <label>Preço de custo</label>
            <input type="text" name="cost_price" class="input" value="<?= e(old('cost_price', isset($product['cost_price']) && $product['cost_price'] ? number_format((float)$product['cost_price'], 2, '.', '') : '')) ?>">
        </div>
        <div class="form-group">
            <label>Estoque mínimo</label>
            <input type="number" name="min_stock" class="input" value="<?= e(old('min_stock', (string)($product['min_stock'] ?? 5))) ?>">
        </div>
        <?php if (!$product): ?>
        <div class="form-group">
            <label>Estoque inicial</label>
            <input type="number" name="initial_stock" class="input" value="0" min="0">
        </div>
        <?php endif; ?>
        <div class="form-group full-width">
            <label>Descrição</label>
            <textarea name="description" class="input" rows="4"><?= e(old('description', $product['description'] ?? '')) ?></textarea>
        </div>
        <div class="form-group full-width">
            <label>Fotos (máx. 5, JPG/PNG/WebP)</label>
            <input type="file" name="photos[]" class="input" accept="image/jpeg,image/png,image/webp" multiple>
            <?php if (!empty($images)): ?>
                <div class="photo-grid">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= url('uploads/produtos/' . e($img['filename'])) ?>" alt="Foto">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="form-group form-check">
            <label><input type="checkbox" name="active" <?= ($product['active'] ?? 1) ? 'checked' : '' ?>> Ativo</label>
        </div>
    </div>
    <div class="form-actions">
        <a href="<?= url('/produtos') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>
