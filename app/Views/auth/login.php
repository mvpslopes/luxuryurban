<?php $title = 'Login'; ?>
<form method="POST" action="<?= url('/login') ?>" class="auth-form">
    <?= csrf_field() ?>
    <div class="field">
        <label for="username">Usuário</label>
        <input type="text" id="username" name="username" required autofocus
               autocomplete="username"
               value="<?= e($old['username'] ?? '') ?>">
    </div>
    <div class="field">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required
               autocomplete="current-password">
    </div>
    <button type="submit" class="btn-submit">Entrar</button>
</form>
