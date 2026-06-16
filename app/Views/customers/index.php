<?php
$title = 'Clientes';
$hasErrors = !empty($_SESSION['_errors'] ?? []);
$headerAction = '<button type="button" class="btn btn-primary" onclick="openModal(\'modal-cliente\')">Novo cliente</button>';
?>

<div class="card mb-3">
    <form method="GET" class="filter-bar">
        <input type="text" name="q" class="input" placeholder="Nome, CPF ou telefone..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
    </form>
</div>

<div class="card">
    <div class="table-toolbar">
        <h2>Clientes</h2>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th><th>Documento</th><th>Telefone</th><th>Vendas</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= e($c['document'] ?? '-') ?></td>
                    <td><?= e($c['phone'] ?? '-') ?></td>
                    <td><?= (int)$c['sales_count'] ?></td>
                    <td>
                        <button type="button" class="btn btn-ghost btn-sm"
                                data-modal-open="modal-cliente"
                                data-action="<?= url("/clientes/{$c['id']}") ?>"
                                data-name="<?= e($c['name']) ?>"
                                data-document="<?= e($c['document'] ?? '') ?>"
                                data-email="<?= e($c['email'] ?? '') ?>"
                                data-phone="<?= e($c['phone'] ?? '') ?>"
                                data-address_street="<?= e($c['address_street'] ?? '') ?>"
                                data-address_number="<?= e($c['address_number'] ?? '') ?>"
                                data-address_neighborhood="<?= e($c['address_neighborhood'] ?? '') ?>"
                                data-address_city="<?= e($c['address_city'] ?? '') ?>"
                                data-address_state="<?= e($c['address_state'] ?? '') ?>"
                                data-address_zip="<?= e($c['address_zip'] ?? '') ?>"
                                data-notes="<?= e($c['notes'] ?? '') ?>">
                            Editar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal-backdrop" id="modal-cliente">
    <div class="modal modal-lg">
        <div class="modal-header">
            <span class="modal-title">Cliente</span>
            <button type="button" class="modal-close" onclick="closeModal('modal-cliente')" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <form method="POST" id="form-cliente" action="<?= url('/clientes') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cl-name">Nome *</label>
                        <input type="text" id="cl-name" name="name" class="input"
                               value="<?= e(old('name')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cl-document">CPF / CNPJ</label>
                        <input type="text" id="cl-document" name="document" class="input"
                               value="<?= e(old('document')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-email">E-mail</label>
                        <input type="email" id="cl-email" name="email" class="input"
                               value="<?= e(old('email')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-phone">Telefone</label>
                        <input type="text" id="cl-phone" name="phone" class="input"
                               value="<?= e(old('phone')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-street">Rua</label>
                        <input type="text" id="cl-street" name="address_street" class="input"
                               value="<?= e(old('address_street')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-number">Número</label>
                        <input type="text" id="cl-number" name="address_number" class="input"
                               value="<?= e(old('address_number')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-neighborhood">Bairro</label>
                        <input type="text" id="cl-neighborhood" name="address_neighborhood" class="input"
                               value="<?= e(old('address_neighborhood')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-city">Cidade</label>
                        <input type="text" id="cl-city" name="address_city" class="input"
                               value="<?= e(old('address_city')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-state">UF</label>
                        <input type="text" id="cl-state" name="address_state" class="input"
                               maxlength="2" value="<?= e(old('address_state')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="cl-zip">CEP</label>
                        <input type="text" id="cl-zip" name="address_zip" class="input"
                               value="<?= e(old('address_zip')) ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="cl-notes">Observações</label>
                        <textarea id="cl-notes" name="notes" class="input" rows="2"><?= e(old('notes')) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-cliente')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
/* Notes field: data attribute value does not auto-fill via form.elements — set manually */
document.querySelectorAll('[data-modal-open="modal-cliente"]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var notes = document.getElementById('cl-notes');
        if (notes) notes.value = this.dataset.notes || '';
    });
});
</script>

<?php if ($hasErrors): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modal-cliente'); });</script>
<?php endif; ?>
