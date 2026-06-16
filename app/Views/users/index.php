<?php
$title = 'Usuários';
$hasErrors = !empty($_SESSION['_errors'] ?? []);
$headerAction = '<button type="button" class="btn btn-primary" onclick="openModal(\'modal-usuario\')">Novo usuário</button>';
?>

<div class="card">
    <div class="table-toolbar">
        <h2>Usuários</h2>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th><th>Usuário</th><th>Perfil</th><th>Status</th><th>Criado em</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><span class="badge badge-info"><?= e(role_label($u['role'])) ?></span></td>
                    <td><?= $u['active'] ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-neutral">Inativo</span>' ?></td>
                    <td><?= format_date($u['created_at']) ?></td>
                    <td>
                        <button type="button" class="btn btn-ghost btn-sm"
                                data-modal-open="modal-usuario"
                                data-action="<?= url("/usuarios/{$u['id']}") ?>"
                                data-name="<?= e($u['name']) ?>"
                                data-username="<?= e($u['username']) ?>"
                                data-email="<?= e($u['email'] ?? '') ?>"
                                data-role="<?= e($u['role']) ?>"
                                data-active="<?= $u['active'] ? '1' : '0' ?>">
                            Editar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Usuário -->
<div class="modal-backdrop" id="modal-usuario">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Usuário</span>
            <button type="button" class="modal-close" onclick="closeModal('modal-usuario')" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <form method="POST" id="form-usuario" action="<?= url('/usuarios') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="u-name">Nome *</label>
                        <input type="text" id="u-name" name="name" class="input"
                               value="<?= e(old('name')) ?>" required>
                        <?php if (!empty($_SESSION['_errors']['name'] ?? '')): ?>
                            <span class="error"><?= e($_SESSION['_errors']['name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="u-username">Login *</label>
                        <input type="text" id="u-username" name="username" class="input"
                               value="<?= e(old('username')) ?>" required autocomplete="off">
                        <?php if (!empty($_SESSION['_errors']['username'] ?? '')): ?>
                            <span class="error"><?= e($_SESSION['_errors']['username']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="u-email">E-mail</label>
                        <input type="email" id="u-email" name="email" class="input"
                               value="<?= e(old('email')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="u-role">Perfil *</label>
                        <select id="u-role" name="role" class="input">
                            <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="vendedor" <?= old('role') === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="u-password">Senha <span id="u-pass-hint" class="text-muted">(obrigatória no cadastro)</span></label>
                        <input type="password" id="u-password" name="password" class="input"
                               autocomplete="new-password">
                        <?php if (!empty($_SESSION['_errors']['password'] ?? '')): ?>
                            <span class="error"><?= e($_SESSION['_errors']['password']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="align-self:flex-end;">
                        <label class="form-check">
                            <input type="checkbox" id="u-active" name="active" checked>
                            Ativo
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-usuario')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
/* Adjust password hint when editing vs creating */
document.querySelectorAll('[data-modal-open="modal-usuario"]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var isEdit = !!this.dataset.action.match(/\/\d+$/);
        var hint = document.getElementById('u-pass-hint');
        var passInput = document.getElementById('u-password');
        if (isEdit) {
            hint.textContent = '(deixe em branco para manter)';
            passInput.removeAttribute('required');
        } else {
            hint.textContent = '(obrigatória no cadastro)';
            passInput.setAttribute('required', 'required');
        }
    });
});
</script>

<?php if ($hasErrors): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ openModal('modal-usuario'); });</script>
<?php endif; ?>
