<form method="POST" action="<?= url($user ? "/usuarios/{$user['id']}" : '/usuarios') ?>" class="card form-card">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="form-group">
            <label>Nome *</label>
            <input type="text" name="name" class="input" value="<?= e(old('name', $user['name'] ?? '')) ?>" required>
            <?php if (!empty($errors['name'])): ?><span class="error"><?= e($errors['name']) ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Usuário *</label>
            <input type="text" name="username" class="input" value="<?= e(old('username', $user['username'] ?? '')) ?>" required>
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" class="input" value="<?= e(old('email', $user['email'] ?? '')) ?>">
        </div>
        <div class="form-group">
            <label>Perfil *</label>
            <?php if (($user['role'] ?? '') === 'root'): ?>
                <input type="text" class="input" value="Root" disabled>
                <input type="hidden" name="role" value="root">
            <?php else: ?>
                <select name="role" class="input">
                    <option value="admin" <?= old('role', $user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="vendedor" <?= old('role', $user['role'] ?? '') === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                </select>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label><?= $user ? 'Nova senha (opcional)' : 'Senha *' ?></label>
            <input type="password" name="password" class="input" <?= $user ? '' : 'required' ?>>
        </div>
        <div class="form-group form-check">
            <label><input type="checkbox" name="active" <?= old('active', ($user['active'] ?? 1)) ? 'checked' : '' ?>> Ativo</label>
        </div>
    </div>
    <div class="form-actions">
        <a href="<?= url('/usuarios') ?>" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>
