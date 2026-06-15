(function () {
    'use strict';

    // Simple chart on dashboard
    if (typeof window.chartData !== 'undefined') {
        const canvas = document.getElementById('salesChart');
        if (canvas && canvas.getContext) {
            const ctx = canvas.getContext('2d');
            const data = window.chartData;
            const keys = Object.keys(data);
            const values = keys.map(k => parseFloat(data[k]) || 0);
            const w = canvas.width = canvas.parentElement.clientWidth - 40;
            const h = canvas.height = 200;
            const max = Math.max(...values, 1);
            const pad = 30;

            ctx.strokeStyle = '#2D2D2D';
            ctx.beginPath();
            ctx.moveTo(pad, h - pad);
            ctx.lineTo(w - pad, h - pad);
            ctx.stroke();

            if (keys.length > 0) {
                ctx.strokeStyle = '#3B82F6';
                ctx.lineWidth = 2;
                ctx.beginPath();
                keys.forEach((k, i) => {
                    const x = pad + (i / Math.max(keys.length - 1, 1)) * (w - pad * 2);
                    const y = h - pad - (values[i] / max) * (h - pad * 2);
                    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                });
                ctx.stroke();
            }
        }
    }

    // PDV
    const pdvForm = document.getElementById('pdv-form');
    if (!pdvForm || !window.PDV_API) return;

    let cart = [];
    let selectedCustomer = null;

    const fmt = (n) => 'R$ ' + n.toFixed(2).replace('.', ',');
    const cartBody = document.getElementById('cart-body');
    const itemsInput = document.getElementById('items_json');
    const customerInput = document.getElementById('customer_id');

    function renderCart() {
        if (cart.length === 0) {
            cartBody.innerHTML = '<tr class="empty-row"><td colspan="5">Nenhum item</td></tr>';
        } else {
            cartBody.innerHTML = cart.map((item, idx) => `
                <tr>
                    <td>${escapeHtml(item.product_name)}<br><small>${escapeHtml(item.sku || '')}</small></td>
                    <td>${fmt(item.unit_price)}</td>
                    <td><input type="number" min="1" max="${item.stock}" value="${item.quantity}" data-idx="${idx}" class="input qty-input" style="width:70px"></td>
                    <td>${fmt(item.unit_price * item.quantity)}</td>
                    <td><button type="button" class="btn btn-ghost btn-sm" data-remove="${idx}">×</button></td>
                </tr>
            `).join('');
        }
        itemsInput.value = JSON.stringify(cart.map(i => ({
            product_id: i.product_id,
            product_name: i.product_name,
            quantity: i.quantity,
            unit_price: i.unit_price
        })));
        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce((s, i) => s + i.unit_price * i.quantity, 0);
        const discType = document.getElementById('discount_type').value;
        const discVal = parseFloat(document.getElementById('discount_value').value.replace(',', '.')) || 0;
        let discount = discType === 'percent' ? subtotal * (discVal / 100) : Math.min(discVal, subtotal);
        const total = Math.max(0, subtotal - discount);
        document.getElementById('subtotal').textContent = fmt(subtotal);
        document.getElementById('discount_display').textContent = fmt(discount);
        document.getElementById('total').textContent = fmt(total);
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function setupSearch(inputId, resultsId, url, onSelect) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        let timer;

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { results.classList.remove('show'); return; }
            timer = setTimeout(async () => {
                const res = await fetch(url + '?q=' + encodeURIComponent(q));
                const data = await res.json();
                results.innerHTML = data.length ? data.map(item => {
                    const label = item.name + (item.sku ? ' (' + item.sku + ')' : '') + (item.stock !== undefined ? ' — Est: ' + item.stock : '');
                    return `<div class="search-item" data-json='${JSON.stringify(item).replace(/'/g, "&#39;")}'>${escapeHtml(label)}</div>`;
                }).join('') : '<div class="search-item">Nenhum resultado</div>';
                results.classList.add('show');
            }, 300);
        });

        results.addEventListener('click', (e) => {
            const el = e.target.closest('.search-item');
            if (!el || !el.dataset.json) return;
            onSelect(JSON.parse(el.dataset.json));
            results.classList.remove('show');
            input.value = '';
        });

        document.addEventListener('click', (e) => {
            if (!results.contains(e.target) && e.target !== input) results.classList.remove('show');
        });
    }

    setupSearch('customer_search', 'customer_results', PDV_API.customers, (c) => {
        selectedCustomer = c;
        customerInput.value = c.id;
        const chip = document.getElementById('selected_customer');
        chip.textContent = c.name;
        chip.classList.remove('hidden');
    });

    setupSearch('product_search', 'product_results', PDV_API.products, (p) => {
        if (p.stock <= 0) { alert('Produto sem estoque.'); return; }
        const existing = cart.find(i => i.product_id === p.id);
        if (existing) {
            if (existing.quantity < p.stock) existing.quantity++;
        } else {
            cart.push({
                product_id: p.id,
                product_name: p.name,
                sku: p.sku,
                unit_price: parseFloat(p.price),
                quantity: 1,
                stock: parseInt(p.stock)
            });
        }
        renderCart();
    });

    cartBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('qty-input')) {
            const idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value) || 1;
            val = Math.max(1, Math.min(val, cart[idx].stock));
            cart[idx].quantity = val;
            renderCart();
        }
    });

    cartBody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove]');
        if (btn) {
            cart.splice(parseInt(btn.dataset.remove), 1);
            renderCart();
        }
    });

    document.getElementById('discount_type').addEventListener('change', updateTotals);
    document.getElementById('discount_value').addEventListener('input', updateTotals);

    pdvForm.addEventListener('submit', (e) => {
        if (!customerInput.value) {
            e.preventDefault();
            alert('Selecione um cliente.');
            return;
        }
        if (cart.length === 0) {
            e.preventDefault();
            alert('Adicione produtos ao carrinho.');
        }
    });
})();
