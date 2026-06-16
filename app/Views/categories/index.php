<?php
$title = 'Categorias';
$hasErrors = !empty($_SESSION['_errors'] ?? []);
$headerAction = '<button type="button" class="btn btn-primary" onclick="openModal(\'modal-categoria\')">Nova categoria</button>';
?>

<div class="card">
    <div class="table-toolbar">
        <h2>Categorias</h2>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th><th>Ordem</th><th>Produtos</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= (int)$c['sort_order'] ?></td>
                    <td><?= (int)$c['products_count'] ?></td>
                    <td><?= $c['active'] ? '<span class="badge badge-success">Ativa</span>' : '<span class="badge badge-neutral">Inativa</span>' ?></td>
                    <td>
                        <button type="button" class="btn btn-ghost btn-sm"
                                data-modal-open="modal-categoria"
                                data-action="<?= url("/categorias/{$c['id']}") ?>"
                                data-name="<?= e($c['name']) ?>"
                                data-sort_order="<?= (int)$c['sort_order'] ?>"
                                data-active="<?= $c['active'] ? '1' : '0' ?>">
                            Editar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Categoria -->
<div class="modal-backdrop" id="modal-categoria">
    <div class="modal modal-sm">
        <div class="modal-header">
            <span class="modal-title">Categoria</span>
            <button type="button" class="modal-close" onclick="closeModal('modal-categoria')" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <form method="POST" id="form-categoria" action="<?= url('/categorias') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="cat-name">Nome *</label>
                    <input type="text" id="cat-name" name="name" class="input"
                           value="<?= e(old('name')) ?>" required autofocus>
                    <?php if (!empty($_SESSION['_errors']['name'] ?? '')): ?>
                        <span class="error"><?= e($_SESSION['_errors']['name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group mb-3">
                    <label for="cat-order">Ordem</label>
                    <input type="number" id="cat-order" name="sort_order" class="input"
                           value="<?= e(old('sort_order', '0')) ?>">
                </div>
                <div class="form-group form-check">
                    <label>
                        <input type="checkbox" id="cat-active" name="active" checked>
                        Ativa
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-categoria')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php if ($hasErrors): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modal-categoria'); });</script>
<?php endif; ?>
