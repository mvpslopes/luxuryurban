<?php
$title = 'Formas de pagamento';
$hasErrors = !empty($_SESSION['_errors'] ?? []);
$headerAction = '<button type="button" class="btn btn-primary" onclick="openModal(\'modal-pagamento\')">Nova forma</button>';
?>

<div class="card">
    <div class="table-toolbar">
        <h2>Formas de pagamento</h2>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th><th>Ordem</th><th>Vendas</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($methods as $m): ?>
                <tr>
                    <td><?= e($m['name']) ?></td>
                    <td><?= (int)$m['sort_order'] ?></td>
                    <td><?= (int)$m['sales_count'] ?></td>
                    <td><?= $m['active'] ? '<span class="badge badge-success">Ativa</span>' : '<span class="badge badge-neutral">Inativa</span>' ?></td>
                    <td>
                        <button type="button" class="btn btn-ghost btn-sm"
                                data-modal-open="modal-pagamento"
                                data-action="<?= url("/formas-pagamento/{$m['id']}") ?>"
                                data-name="<?= e($m['name']) ?>"
                                data-sort_order="<?= (int)$m['sort_order'] ?>"
                                data-active="<?= $m['active'] ? '1' : '0' ?>">
                            Editar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Forma de pagamento -->
<div class="modal-backdrop" id="modal-pagamento">
    <div class="modal modal-sm">
        <div class="modal-header">
            <span class="modal-title">Forma de pagamento</span>
            <button type="button" class="modal-close" onclick="closeModal('modal-pagamento')" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <form method="POST" id="form-pagamento" action="<?= url('/formas-pagamento') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="pay-name">Nome *</label>
                    <input type="text" id="pay-name" name="name" class="input"
                           value="<?= e(old('name')) ?>" required autofocus>
                    <?php if (!empty($_SESSION['_errors']['name'] ?? '')): ?>
                        <span class="error"><?= e($_SESSION['_errors']['name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group mb-3">
                    <label for="pay-order">Ordem</label>
                    <input type="number" id="pay-order" name="sort_order" class="input"
                           value="<?= e(old('sort_order', '0')) ?>">
                </div>
                <div class="form-group form-check">
                    <label>
                        <input type="checkbox" id="pay-active" name="active" checked>
                        Ativa
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-pagamento')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php if ($hasErrors): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modal-pagamento'); });</script>
<?php endif; ?>
