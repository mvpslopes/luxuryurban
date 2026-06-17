<form method="POST" action="<?= url($customer ? "/clientes/{$customer['id']}" : '/clientes') ?>" class="card form-card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="form-group"><label>Nome *</label><input type="text" name="name" class="input" value="<?= e(old('name', $customer['name'] ?? '')) ?>" required></div>
        <div class="form-group"><label>CPF/CNPJ</label><input type="text" name="document" class="input" value="<?= e(old('document', $customer['document'] ?? '')) ?>"></div>
        <div class="form-group"><label>E-mail</label><input type="email" name="email" class="input" value="<?= e(old('email', $customer['email'] ?? '')) ?>"></div>
        <div class="form-group"><label>Telefone</label><input type="text" name="phone" class="input" value="<?= e(old('phone', $customer['phone'] ?? '')) ?>"></div>
        <div class="form-group"><label>Rua</label><input type="text" name="address_street" class="input" value="<?= e(old('address_street', $customer['address_street'] ?? '')) ?>"></div>
        <div class="form-group"><label>Número</label><input type="text" name="address_number" class="input" value="<?= e(old('address_number', $customer['address_number'] ?? '')) ?>"></div>
        <div class="form-group"><label>Bairro</label><input type="text" name="address_neighborhood" class="input" value="<?= e(old('address_neighborhood', $customer['address_neighborhood'] ?? '')) ?>"></div>
        <div class="form-group"><label>Cidade</label><input type="text" name="address_city" class="input" value="<?= e(old('address_city', $customer['address_city'] ?? '')) ?>"></div>
        <div class="form-group"><label>UF</label><input type="text" name="address_state" class="input" maxlength="2" value="<?= e(old('address_state', $customer['address_state'] ?? '')) ?>"></div>
        <div class="form-group"><label>CEP</label><input type="text" name="address_zip" class="input" value="<?= e(old('address_zip', $customer['address_zip'] ?? '')) ?>"></div>
        <div class="form-group full-width"><label>Observações</label><textarea name="notes" class="input" rows="3"><?= e(old('notes', $customer['notes'] ?? '')) ?></textarea></div>
    </div>
    <div class="form-actions">
        <a href="<?= url('/clientes') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>

<?php if (!empty($sales)): ?>
<div class="card mt-3">
    <h2 class="card-title">Histórico de compras</h2>
    <div class="table-wrap">
    <table class="table">
        <thead><tr><th>Recibo</th><th>Total</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
        <?php foreach ($sales as $s): ?>
            <tr>
                <td><?= e($s['receipt_number']) ?></td>
                <td><?= money((float)$s['total']) ?></td>
                <td><span class="badge <?= sale_status_class($s['status']) ?>"><?= sale_status_label($s['status']) ?></span></td>
                <td><?= format_date($s['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>
