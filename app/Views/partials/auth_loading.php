<div class="auth-loading hidden" id="authLoading" role="status" aria-live="polite" aria-label="Carregando sistema de vendas">
    <div class="auth-loading__bg" aria-hidden="true"></div>
    <div class="auth-loading__content">
        <img src="<?= asset('logo/logo.png') ?>" alt="" class="auth-loading__logo">
        <p class="auth-loading__text">Carregando sistema de vendas</p>
        <?php $progressId = 'authLoadingProgress'; require base_path('app/Views/partials/progress_bar.php'); ?>
    </div>
</div>
