<script>
(function () {
    try {
        if (sessionStorage.getItem('luxury_splash_seen')) {
            document.documentElement.classList.add('splash-skip');
        }
    } catch (e) {}
})();
</script>
<div class="app-splash" id="appSplash" aria-hidden="true">
    <div class="app-splash__bg" aria-hidden="true"></div>
    <div class="app-splash__content">
        <img src="<?= asset('logo/logo.png') ?>" alt="Luxury Urban" class="app-splash__logo">
    </div>
</div>
<script>
(function () {
    var KEY = 'luxury_splash_seen';
    var splash = document.getElementById('appSplash');
    if (!splash) return;

    try {
        if (sessionStorage.getItem(KEY)) {
            splash.remove();
            return;
        }
    } catch (e) {}

    var minMs = 1200;
    var start = Date.now();

    function hide() {
        try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
        var wait = Math.max(0, minMs - (Date.now() - start));
        setTimeout(function () {
            splash.classList.add('is-hidden');
            setTimeout(function () { splash.remove(); }, 520);
        }, wait);
    }

    if (document.readyState === 'complete') hide();
    else window.addEventListener('load', hide);
}());
</script>
