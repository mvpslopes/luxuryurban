<?php $title = 'Login'; ?>
<form method="POST" action="<?= url('/login') ?>" class="form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="username">Usuário</label>
        <input type="text" id="username" name="username" class="input" required autofocus>
    </div>
    <div class="form-group">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" class="input" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Entrar</button>
</form>
