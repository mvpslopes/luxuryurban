<?php
/** @var string $progressId */
$progressId = $progressId ?? 'luxuryProgress';
?>
<div class="luxury-progress" id="<?= e($progressId) ?>" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
    <div class="luxury-progress__track">
        <div class="luxury-progress__fill"></div>
    </div>
    <span class="luxury-progress__label">0%</span>
</div>
