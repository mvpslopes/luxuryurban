<?php $title = 'Estornar venda ' . $sale['receipt_number']; ?>
<div class="card form-card">
    <p>Estorno <strong>completo</strong> da venda <?= e($sale['receipt_number']) ?> — Total: <?= money((float)$sale['total']) ?></p>
    <div class="table-wrap mb-3">
    <table class="table">
        <thead><tr><th>Produto</th><th>Qtd</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['product_name']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= money((float)$item['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <form method="POST" action="<?= url("/vendas/{$sale['id']}/estornar") ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Motivo do estorno *</label>
            <textarea name="reason" class="input" rows="4" required></textarea>
        </div>
        <div class="form-actions">
            <a href="<?= url("/vendas/{$sale['id']}") ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-danger">Confirmar estorno</button>
        </div>
    </form>
</div>
