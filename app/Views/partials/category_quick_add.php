<?php
/**
 * Botão + e modal rápido para criar categoria inline.
 * Variáveis: $categorySelectId (id do <select>), $modalId (opcional)
 */
$categorySelectId = $categorySelectId ?? 'category_id';
$modalId = $modalId ?? 'modal-categoria-rapida';
?>
<div class="field-with-btn">
    <select id="<?= e($categorySelectId) ?>" name="category_id" class="input" required>
        <option value="">Selecione...</option>
        <?php foreach ($categories as $c): ?>
            <?php if (!$c['active'] && (string)($selectedCategoryId ?? '') !== (string)$c['id']) continue; ?>
            <option value="<?= $c['id'] ?>" <?= (string)old('category_id', $selectedCategoryId ?? '') === (string)$c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="button" class="btn btn-secondary btn-add" onclick="openQuickCategoryModal('<?= e($modalId) ?>')" aria-label="Nova categoria" title="Nova categoria">
        <?= icon('plus', 18) ?>
    </button>
</div>

<div class="modal-backdrop modal-stacked" id="<?= e($modalId) ?>">
    <div class="modal modal-sm">
        <div class="modal-header">
            <span class="modal-title">Nova categoria</span>
            <button type="button" class="modal-close" onclick="closeModal('<?= e($modalId) ?>')" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="<?= e($modalId) ?>-name">Nome da categoria *</label>
                <input type="text" id="<?= e($modalId) ?>-name" class="input" placeholder="Ex: Roupas" autofocus>
                <span class="error" id="<?= e($modalId) ?>-error"></span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('<?= e($modalId) ?>')">Cancelar</button>
            <button type="button" class="btn btn-primary" id="<?= e($modalId) ?>-save">Salvar</button>
        </div>
    </div>
</div>

<script>
(function () {
    var selectId = <?= json_encode($categorySelectId) ?>;
    var modalId = <?= json_encode($modalId) ?>;
    var apiUrl = <?= json_encode(url('/api/categorias')) ?>;

    window.openQuickCategoryModal = window.openQuickCategoryModal || function (id) {
        var nameInput = document.getElementById(id + '-name');
        var errorEl = document.getElementById(id + '-error');
        if (nameInput) nameInput.value = '';
        if (errorEl) errorEl.textContent = '';
        openModal(id);
        if (nameInput) setTimeout(function () { nameInput.focus(); }, 80);
    };

    var saveBtn = document.getElementById(modalId + '-save');
    if (!saveBtn || saveBtn.dataset.bound === '1') return;
    saveBtn.dataset.bound = '1';

    saveBtn.addEventListener('click', async function () {
        var nameInput = document.getElementById(modalId + '-name');
        var errorEl = document.getElementById(modalId + '-error');
        var name = (nameInput && nameInput.value || '').trim();
        if (!name) {
            if (errorEl) errorEl.textContent = 'Informe o nome da categoria.';
            return;
        }

        var csrfInput = document.querySelector('input[name="_csrf"]');
        if (!csrfInput) return;

        saveBtn.disabled = true;
        if (errorEl) errorEl.textContent = '';

        try {
            var body = new FormData();
            body.append('name', name);
            body.append('_csrf', csrfInput.value);

            var res = await fetch(apiUrl, { method: 'POST', body: body });
            var data = await res.json();

            if (!res.ok) {
                if (errorEl) errorEl.textContent = data.error || 'Erro ao criar categoria.';
                return;
            }

            var select = document.getElementById(selectId);
            if (select) {
                var exists = Array.from(select.options).some(function (o) {
                    return String(o.value) === String(data.id);
                });
                if (!exists) {
                    var opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = data.name;
                    select.appendChild(opt);
                }
                select.value = String(data.id);
            }

            closeModal(modalId);
        } catch (e) {
            if (errorEl) errorEl.textContent = 'Erro de conexão. Tente novamente.';
        } finally {
            saveBtn.disabled = false;
        }
    });

    var nameInput = document.getElementById(modalId + '-name');
    if (nameInput) {
        nameInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveBtn.click();
            }
        });
    }
})();
</script>
