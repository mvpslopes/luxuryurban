<?php $title = 'Nova Venda'; ?>
<form method="POST" action="<?= url('/vendas') ?>" id="pdv-form" class="pdv-layout">
    <?= csrf_field() ?>
    <input type="hidden" name="items_json" id="items_json" value="[]">
    <input type="hidden" name="customer_id" id="customer_id" value="">

    <div class="pdv-col">
        <div class="card pdv-panel">
            <h2 class="card-title">Cliente</h2>
            <input type="text" id="customer_filter" class="input" placeholder="Filtrar clientes..." autocomplete="off">
            <div id="customer_list" class="pdv-list" role="listbox" aria-label="Lista de clientes"></div>
            <div id="selected_customer" class="selected-chip hidden"></div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modal-cliente-pdv')">
                <?= icon('plus', 16) ?> Novo cliente
            </button>
        </div>
    </div>

    <div class="pdv-col">
        <div class="card pdv-panel">
            <h2 class="card-title">Produtos</h2>
            <input type="text" id="product_filter" class="input" placeholder="Filtrar produtos ou SKU..." autocomplete="off">
            <div id="product_list" class="pdv-list" aria-label="Lista de produtos"></div>
        </div>
    </div>

    <div class="pdv-col pdv-main">
        <div class="card">
            <h2 class="card-title">Carrinho</h2>
            <div class="table-wrap">
                <table class="table" id="cart-table">
                    <thead>
                        <tr><th>Produto</th><th>Preço</th><th>Qtd</th><th>Subtotal</th><th></th></tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr class="empty-row"><td colspan="5">Nenhum item</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pdv-summary">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Desconto</label>
                        <div class="input-group">
                            <select name="discount_type" id="discount_type" class="input">
                                <option value="percent">%</option>
                                <option value="fixed">R$</option>
                            </select>
                            <input type="text" name="discount_value" id="discount_value" class="input" value="0">
                        </div>
                        <?php if (\App\Core\Auth::isVendedor()): ?>
                            <small class="text-muted">Limite: 10% (acima requer aprovação Admin)</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Forma de pagamento *</label>
                        <select name="payment_method_id" class="input" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($paymentMethods as $pm): ?>
                                <option value="<?= $pm['id'] ?>"><?= e($pm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Observações</label>
                        <textarea name="notes" class="input" rows="2"></textarea>
                    </div>
                </div>

                <div class="totals">
                    <div><span>Subtotal</span><strong id="subtotal">R$ 0,00</strong></div>
                    <div><span>Desconto</span><strong id="discount_display">R$ 0,00</strong></div>
                    <div class="total-line"><span>Total</span><strong id="total">R$ 0,00</strong></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="submit-sale">Confirmar venda</button>
            </div>
        </div>
    </div>
</form>

<!-- Modal rápido: novo cliente no PDV -->
<div class="modal-backdrop modal-stacked" id="modal-cliente-pdv">
    <div class="modal modal-sm">
        <div class="modal-header">
            <span class="modal-title">Novo cliente</span>
            <button type="button" class="modal-close" onclick="closeModal('modal-cliente-pdv')" aria-label="Fechar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group mb-3">
                <label for="pdv-client-name">Nome *</label>
                <input type="text" id="pdv-client-name" class="input" required>
            </div>
            <div class="form-group mb-3">
                <label for="pdv-client-document">CPF / CNPJ</label>
                <input type="text" id="pdv-client-document" class="input">
            </div>
            <div class="form-group">
                <label for="pdv-client-phone">Telefone</label>
                <input type="text" id="pdv-client-phone" class="input">
            </div>
            <span class="error" id="pdv-client-error"></span>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-cliente-pdv')">Cancelar</button>
            <button type="button" class="btn btn-primary" id="pdv-client-save">Salvar</button>
        </div>
    </div>
</div>

<script>
window.PDV_DATA = <?= json_encode([
    'customers' => array_map(static fn ($c) => [
        'id' => (int) $c['id'],
        'name' => $c['name'],
        'document' => $c['document'],
        'phone' => $c['phone'],
    ], $customers),
    'products' => array_map(static fn ($p) => [
        'id' => (int) $p['id'],
        'name' => $p['name'],
        'sku' => $p['sku'],
        'price' => (float) $p['price'],
        'stock' => (int) $p['stock'],
    ], $products),
    'customerApi' => url('/api/clientes'),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
</script>
