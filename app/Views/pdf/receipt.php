<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 22px; }
        .meta { margin-bottom: 20px; }
        .meta p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .totals { text-align: right; margin-top: 16px; }
        .totals p { margin: 4px 0; }
        .footer { margin-top: 32px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Luxury Urban</h1>
        <p>Recibo de venda</p>
    </div>
    <div class="meta">
        <p><strong>Recibo:</strong> <?= htmlspecialchars($sale['receipt_number']) ?></p>
        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></p>
        <p><strong>Cliente:</strong> <?= htmlspecialchars($sale['customer_name']) ?></p>
        <p><strong>Vendedor:</strong> <?= htmlspecialchars($sale['seller_name']) ?></p>
        <p><strong>Pagamento:</strong> <?= htmlspecialchars($sale['payment_method_name']) ?></p>
    </div>
    <table>
        <thead>
            <tr><th>Produto</th><th>Qtd</th><th>Preço unit.</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td>R$ <?= number_format((float)$item['unit_price'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="totals">
        <p>Subtotal: R$ <?= number_format((float)$sale['subtotal'], 2, ',', '.') ?></p>
        <p>Desconto: R$ <?= number_format((float)$sale['discount_amount'], 2, ',', '.') ?></p>
        <p><strong>Total: R$ <?= number_format((float)$sale['total'], 2, ',', '.') ?></strong></p>
    </div>
    <div class="footer">luxuryurban.com.br</div>
</body>
</html>
