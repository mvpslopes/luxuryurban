(function () {
    'use strict';

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
    }

    /* ── Modal system ─────────────────────────────────────── */
    window.openModal = function (id) {
        var bd = document.getElementById(id);
        if (!bd) return;
        bd.classList.add('open');
        document.body.style.overflow = 'hidden';
        var first = bd.querySelector('input:not([type=hidden]),select,textarea');
        if (first) setTimeout(function () { first.focus(); }, 80);
    };

    window.closeModal = function (id) {
        var bd = document.getElementById(id);
        if (!bd) return;
        bd.classList.remove('open');
        document.body.style.overflow = '';
    };

    /* Close on Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(function (bd) {
                bd.classList.remove('open');
                document.body.style.overflow = '';
            });
        }
    });

    /* data-modal-open triggers */
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-modal-open]');
        if (!trigger) return;
        var id = trigger.dataset.modalOpen;
        var form = document.querySelector('#' + id + ' form');
        if (form && trigger.dataset) {
            /* Populate form fields from data-* attributes */
            Object.keys(trigger.dataset).forEach(function (key) {
                if (key === 'modalOpen') return;
                var field = form.elements[key];
                if (!field) return;
                if (field.type === 'checkbox') {
                    field.checked = trigger.dataset[key] === '1';
                } else {
                    field.value = trigger.dataset[key];
                }
            });
            /* Set form action if provided */
            if (trigger.dataset.action) {
                form.action = trigger.dataset.action;
            }
        }
        openModal(id);
    });

    /* ── Toast ────────────────────────────────────────────── */
    function ensureToastHost() {
        var host = document.getElementById('toastHost');
        if (host) return host;
        host = document.createElement('div');
        host.id = 'toastHost';
        host.className = 'toast-host';
        document.body.appendChild(host);
        return host;
    }

    function toastIcon(type) {
        if (type === 'success') return '✓';
        if (type === 'danger') return '!';
        return 'i';
    }

    window.showToast = function (message, opts) {
        opts = opts || {};
        var type = opts.type || 'warn';
        var title = opts.title || (type === 'danger' ? 'Atenção' : 'Aviso');
        var duration = typeof opts.duration === 'number' ? opts.duration : 2600;

        var host = ensureToastHost();
        var el = document.createElement('div');
        el.className = 'toast toast--' + type;
        el.innerHTML = ''
            + '<div aria-hidden="true" style="margin-top:1px;font-weight:700;min-width:18px;text-align:center;">' + toastIcon(type) + '</div>'
            + '<div style="min-width:0;">'
            +   '<div class="toast__title">' + escapeHtml(String(title)) + '</div>'
            +   '<div class="toast__msg">' + escapeHtml(String(message)) + '</div>'
            + '</div>'
            + '<button type="button" class="toast__close" aria-label="Fechar">×</button>';

        var closeBtn = el.querySelector('.toast__close');
        var done = false;
        function close() {
            if (done) return;
            done = true;
            el.style.transition = 'opacity .15s ease, transform .15s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(6px)';
            setTimeout(function () { el.remove(); }, 160);
        }
        closeBtn.addEventListener('click', close);

        host.appendChild(el);
        if (duration > 0) setTimeout(close, duration);
    };

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

            ctx.strokeStyle = 'rgba(255,255,255,0.12)';
            ctx.beginPath();
            ctx.moveTo(pad, h - pad);
            ctx.lineTo(w - pad, h - pad);
            ctx.stroke();

            if (keys.length > 0) {
                const grad = ctx.createLinearGradient(pad, 0, w - pad, 0);
                grad.addColorStop(0, '#9a7b2c');
                grad.addColorStop(0.5, '#d4af37');
                grad.addColorStop(1, '#f9e498');
                ctx.strokeStyle = grad;
                ctx.lineWidth = 2.5;
                ctx.beginPath();
                keys.forEach((k, i) => {
                    const x = pad + (i / Math.max(keys.length - 1, 1)) * (w - pad * 2);
                    const y = h - pad - (values[i] / max) * (h - pad * 2);
                    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                });
                ctx.stroke();

                ctx.fillStyle = grad;
                keys.forEach((k, i) => {
                    const x = pad + (i / Math.max(keys.length - 1, 1)) * (w - pad * 2);
                    const y = h - pad - (values[i] / max) * (h - pad * 2);
                    ctx.beginPath();
                    ctx.arc(x, y, 4, 0, Math.PI * 2);
                    ctx.fill();
                });
            }
        }
    }

    // PDV
    const pdvForm = document.getElementById('pdv-form');
    if (!pdvForm || !window.PDV_DATA) return;

    const pdvData = window.PDV_DATA;
    let cart = [];
    let selectedCustomer = null;
    let customers = pdvData.customers || [];
    let products = pdvData.products || [];

    const fmt = (n) => 'R$ ' + n.toFixed(2).replace('.', ',');
    const removeIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
    const cartBody = document.getElementById('cart-body');
    const itemsInput = document.getElementById('items_json');
    const customerInput = document.getElementById('customer_id');
    const customerList = document.getElementById('customer_list');
    const productList = document.getElementById('product_list');
    const customerFilter = document.getElementById('customer_filter');
    const productFilter = document.getElementById('product_filter');
    const selectedChip = document.getElementById('selected_customer');

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
    }

    function customerMeta(c) {
        const parts = [];
        if (c.document) parts.push(c.document);
        if (c.phone) parts.push(c.phone);
        return parts.join(' · ') || 'Sem documento/telefone';
    }

    function selectCustomer(c) {
        selectedCustomer = c;
        customerInput.value = c.id;
        if (selectedChip) {
            selectedChip.textContent = 'Cliente: ' + c.name;
            selectedChip.classList.remove('hidden');
        }
        renderCustomerList(customerFilter ? customerFilter.value : '');
    }

    function renderCustomerList(filter) {
        if (!customerList) return;
        const q = (filter || '').trim().toLowerCase();
        const list = customers.filter((c) => {
            if (!q) return true;
            return (c.name || '').toLowerCase().includes(q)
                || (c.document || '').toLowerCase().includes(q)
                || (c.phone || '').toLowerCase().includes(q);
        });

        if (!list.length) {
            customerList.innerHTML = '<div class="pdv-list-empty">Nenhum cliente encontrado</div>';
            return;
        }

        customerList.innerHTML = list.map((c) => {
            const selected = selectedCustomer && String(selectedCustomer.id) === String(c.id);
            return `<button type="button" class="pdv-list-item pdv-list-item--customer${selected ? ' selected' : ''}" data-customer-id="${c.id}">
                <div class="pdv-list-item__info">
                    <div class="pdv-list-item__title">${escapeHtml(c.name)}</div>
                    <div class="pdv-list-item__meta">${escapeHtml(customerMeta(c))}</div>
                </div>
            </button>`;
        }).join('');
    }

    function addProductToCart(p) {
        if (p.stock <= 0) {
            if (typeof window.showToast === 'function') {
                window.showToast('Este produto está sem estoque.', { type: 'danger', title: 'Sem estoque' });
            } else {
                alert('Produto sem estoque.');
            }
            return;
        }
        const existing = cart.find((i) => i.product_id === p.id);
        if (existing) {
            if (existing.quantity < p.stock) existing.quantity++;
            else {
                if (typeof window.showToast === 'function') {
                    window.showToast('Quantidade máxima em estoque atingida.', { type: 'warn', title: 'Limite de estoque' });
                } else {
                    alert('Quantidade máxima em estoque atingida.');
                }
            }
        } else {
            cart.push({
                product_id: p.id,
                product_name: p.name,
                sku: p.sku,
                unit_price: parseFloat(p.price),
                quantity: 1,
                stock: parseInt(p.stock, 10),
            });
        }
        renderCart();
    }

    function renderProductList(filter) {
        if (!productList) return;
        const q = (filter || '').trim().toLowerCase();
        const list = products.filter((p) => {
            if (!q) return true;
            return (p.name || '').toLowerCase().includes(q)
                || (p.sku || '').toLowerCase().includes(q);
        });

        if (!list.length) {
            productList.innerHTML = '<div class="pdv-list-empty">Nenhum produto encontrado</div>';
            return;
        }

        productList.innerHTML = list.map((p) => {
            const noStock = p.stock <= 0;
            const stockLabel = noStock ? 'Sem estoque' : 'Est: ' + p.stock;
            const btnClass = noStock ? 'btn btn-secondary btn-sm btn-out-of-stock' : 'btn btn-primary btn-sm';
            return `<div class="pdv-list-item pdv-list-item--product${noStock ? ' pdv-list-item--no-stock' : ''}">
                <div class="pdv-list-item__info">
                    <div class="pdv-list-item__title">${escapeHtml(p.name)}</div>
                    <div class="pdv-list-item__meta">${escapeHtml(p.sku || '')} · ${stockLabel}</div>
                </div>
                <div class="pdv-list-item__price">${fmt(parseFloat(p.price))}</div>
                <button type="button" class="${btnClass}" data-add-id="${p.id}">Adicionar</button>
            </div>`;
        }).join('');
    }

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
                    <td><button type="button" class="btn btn-ghost btn-sm btn-icon" data-remove="${idx}" aria-label="Remover item">${removeIcon}</button></td>
                </tr>
            `).join('');
        }
        itemsInput.value = JSON.stringify(cart.map((i) => ({
            product_id: i.product_id,
            product_name: i.product_name,
            quantity: i.quantity,
            unit_price: i.unit_price,
        })));
        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce((s, i) => s + i.unit_price * i.quantity, 0);
        const discType = document.getElementById('discount_type').value;
        const discVal = parseFloat(document.getElementById('discount_value').value.replace(',', '.')) || 0;
        const discount = discType === 'percent' ? subtotal * (discVal / 100) : Math.min(discVal, subtotal);
        const total = Math.max(0, subtotal - discount);
        document.getElementById('subtotal').textContent = fmt(subtotal);
        document.getElementById('discount_display').textContent = fmt(discount);
        document.getElementById('total').textContent = fmt(total);
    }

    if (customerFilter) {
        customerFilter.addEventListener('input', () => renderCustomerList(customerFilter.value));
    }
    if (productFilter) {
        productFilter.addEventListener('input', () => renderProductList(productFilter.value));
    }

    if (customerList) {
        customerList.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-customer-id]');
            if (!btn) return;
            const id = parseInt(btn.dataset.customerId, 10);
            const c = customers.find((x) => x.id === id);
            if (c) selectCustomer(c);
        });
    }

    if (productList) {
        productList.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-add-id]');
            if (!btn) return;
            const id = parseInt(btn.dataset.addId, 10);
            const p = products.find((x) => x.id === id);
            if (p) addProductToCart(p);
        });
    }

    const pdvClientSave = document.getElementById('pdv-client-save');
    if (pdvClientSave) {
        pdvClientSave.addEventListener('click', async () => {
            const nameInput = document.getElementById('pdv-client-name');
            const docInput = document.getElementById('pdv-client-document');
            const phoneInput = document.getElementById('pdv-client-phone');
            const errorEl = document.getElementById('pdv-client-error');
            const name = (nameInput && nameInput.value || '').trim();
            if (!name) {
                if (errorEl) errorEl.textContent = 'Informe o nome do cliente.';
                return;
            }

            const csrfInput = document.querySelector('#pdv-form input[name="_csrf"]');
            if (!csrfInput) return;

            pdvClientSave.disabled = true;
            if (errorEl) errorEl.textContent = '';

            try {
                const body = new FormData();
                body.append('name', name);
                body.append('document', docInput ? docInput.value : '');
                body.append('phone', phoneInput ? phoneInput.value : '');
                body.append('_csrf', csrfInput.value);

                const res = await fetch(pdvData.customerApi, { method: 'POST', body });
                const data = await res.json();
                if (!res.ok) {
                    if (errorEl) errorEl.textContent = data.error || 'Erro ao cadastrar cliente.';
                    return;
                }

                customers.push(data);
                customers.sort((a, b) => a.name.localeCompare(b.name, 'pt-BR'));
                selectCustomer(data);
                if (nameInput) nameInput.value = '';
                if (docInput) docInput.value = '';
                if (phoneInput) phoneInput.value = '';
                closeModal('modal-cliente-pdv');
            } catch (err) {
                if (errorEl) errorEl.textContent = 'Erro de conexão. Tente novamente.';
            } finally {
                pdvClientSave.disabled = false;
            }
        });
    }

    renderCustomerList('');
    renderProductList('');
    renderCart();

    cartBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('qty-input')) {
            const idx = parseInt(e.target.dataset.idx, 10);
            let val = parseInt(e.target.value, 10) || 1;
            val = Math.max(1, Math.min(val, cart[idx].stock));
            cart[idx].quantity = val;
            renderCart();
        }
    });

    cartBody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove]');
        if (btn) {
            cart.splice(parseInt(btn.dataset.remove, 10), 1);
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
