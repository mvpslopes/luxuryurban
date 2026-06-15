<?php $title = 'Movimentação de estoque'; ?>
<form method="POST" action="<?= url('/estoque/movimentacao') ?>" class="card form-card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="form-group">
            <label>Produto *</label>
            <select name="product_id" class="input" required>
                <option value="">Selecione...</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= (int)$p['quantity'] ?> un.)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Tipo *</label>
            <select name="type" class="input" required>
                <option value="entrada">Entrada</option>
                <option value="saida">Saída</option>
                <option value="ajuste">Ajuste (define saldo)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Quantidade *</label>
            <input type="number" name="quantity" class="input" min="0" required>
        </div>
        <div class="form-group full-width">
            <label>Observações</label>
            <textarea name="notes" class="input" rows="3"></textarea>
        </div>
    </div>
    <div class="form-actions">
        <a href="<?= url('/estoque') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Registrar</button>
    </div>
</form>
