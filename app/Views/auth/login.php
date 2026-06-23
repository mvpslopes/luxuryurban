<?php $title = 'Login'; ?>
<form method="POST" action="<?= url('/login') ?>" class="auth-form" id="login-form">
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
    <button type="submit" class="btn-submit" id="login-submit">Entrar</button>
</form>
<script>
(function () {
    var form = document.getElementById('login-form');
    var loading = document.getElementById('authLoading');
    var btn = document.getElementById('login-submit');
    var progressRoot = document.getElementById('authLoadingProgress');
    var sending = false;
    if (!form || !loading) return;

    form.addEventListener('submit', function (e) {
        if (sending) return;
        if (!form.checkValidity()) return;

        e.preventDefault();

        loading.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Entrando...';
        }

        if (window.LuxuryProgress && progressRoot) {
            LuxuryProgress.reset(progressRoot);
            LuxuryProgress.animateToFull(progressRoot, 3000, function () {
                sending = true;
                try { sessionStorage.setItem('luxury_splash_seen', '1'); } catch (err) {}
                form.submit();
            });
        } else {
            sending = true;
            try { sessionStorage.setItem('luxury_splash_seen', '1'); } catch (err) {}
            form.submit();
        }
    });
}());
</script>
