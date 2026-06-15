<?php $title = 'Nova Venda'; ?>
<form method="POST" action="<?= url('/vendas') ?>" id="pdv-form" class="pdv-layout">
    <?= csrf_field() ?>
    <input type="hidden" name="items_json" id="items_json" value="[]">
    <input type="hidden" name="customer_id" id="customer_id" value="">

    <div class="pdv-col">
        <div class="card">
            <h2 class="card-title">Cliente</h2>
            <input type="text" id="customer_search" class="input" placeholder="Buscar cliente..." autocomplete="off">
            <div id="customer_results" class="search-results"></div>
            <div id="selected_customer" class="selected-chip hidden"></div>
            <a href="<?= url('/clientes/novo') ?>" class="btn btn-ghost btn-sm mt-2" target="_blank">+ Cadastrar cliente</a>
        </div>

        <div class="card">
            <h2 class="card-title">Produtos</h2>
            <input type="text" id="product_search" class="input" placeholder="Buscar produto ou SKU..." autocomplete="off">
            <div id="product_results" class="search-results"></div>
        </div>
    </div>

    <div class="pdv-col pdv-main">
        <div class="card">
            <h2 class="card-title">Carrinho</h2>
            <table class="table" id="cart-table">
                <thead><tr><th>Produto</th><th>Preço</th><th>Qtd</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="cart-body"><tr class="empty-row"><td colspan="5">Nenhum item</td></tr></tbody>
            </table>

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

<script>window.PDV_API = { products: '<?= url('/api/produtos') ?>', customers: '<?= url('/api/clientes') ?>' };</script>
