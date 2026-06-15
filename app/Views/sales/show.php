<?php
$title = 'Venda ' . $sale['receipt_number'];
$actions = '';
if ($sale['status'] === 'concluida') {
    $actions .= '<a href="' . url("/vendas/{$sale['id']}/pdf") . '" class="btn btn-secondary" target="_blank">PDF</a> ';
    if (\App\Core\Auth::can('sales.refund')) {
        $actions .= '<a href="' . url("/vendas/{$sale['id']}/estornar") . '" class="btn btn-danger">Estornar</a>';
    }
}
$headerAction = $actions;
?>
<div class="grid-2">
    <div class="card">
        <h2 class="card-title">Detalhes</h2>
        <dl class="detail-list">
            <dt>Recibo</dt><dd><?= e($sale['receipt_number']) ?></dd>
            <dt>Cliente</dt><dd><?= e($sale['customer_name']) ?></dd>
            <dt>Vendedor</dt><dd><?= e($sale['seller_name']) ?></dd>
            <dt>Pagamento</dt><dd><?= e($sale['payment_method_name']) ?></dd>
            <dt>Status</dt><dd><span class="badge <?= sale_status_class($sale['status']) ?>"><?= sale_status_label($sale['status']) ?></span></dd>
            <dt>Data</dt><dd><?= format_date($sale['created_at']) ?></dd>
        </dl>
    </div>
    <div class="card">
        <h2 class="card-title">Totais</h2>
        <dl class="detail-list">
            <dt>Subtotal</dt><dd><?= money((float)$sale['subtotal']) ?></dd>
            <dt>Desconto</dt><dd><?= money((float)$sale['discount_amount']) ?></dd>
            <dt>Total</dt><dd><strong><?= money((float)$sale['total']) ?></strong></dd>
        </dl>
    </div>
</div>
<div class="card mt-3">
    <h2 class="card-title">Itens</h2>
    <table class="table">
        <thead><tr><th>Produto</th><th>Qtd</th><th>Preço unit.</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['product_name']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= money((float)$item['unit_price']) ?></td>
                <td><?= money((float)$item['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
