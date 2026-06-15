<form method="POST" action="<?= url($category ? "/categorias/{$category['id']}" : '/categorias') ?>" class="card form-card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="form-group">
            <label>Nome *</label>
            <input type="text" name="name" class="input" value="<?= e(old('name', $category['name'] ?? '')) ?>" required>
        </div>
        <div class="form-group">
            <label>Ordem</label>
            <input type="number" name="sort_order" class="input" value="<?= e(old('sort_order', (string)($category['sort_order'] ?? 0))) ?>">
        </div>
        <div class="form-group form-check">
            <label><input type="checkbox" name="active" <?= ($category['active'] ?? 1) ? 'checked' : '' ?>> Ativa</label>
        </div>
    </div>
    <div class="form-actions">
        <a href="<?= url('/categorias') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>
