'use strict';

const menu = document.querySelector('.menu-toggle');
menu?.addEventListener('click', () => {
    const expanded = menu.getAttribute('aria-expanded') === 'true';
    menu.setAttribute('aria-expanded', String(!expanded));
    document.getElementById('nav-links').classList.toggle('open', !expanded);
});

const money = new Intl.NumberFormat('en-EG');
const product = document.querySelector('[data-product-form]');
if (product) {
    const variants = JSON.parse(product.dataset.variants);
    const render = () => {
        const values = new FormData(product);
        const color = values.get('color');
        const mode = values.get('design_mode');
        const size = values.get('size');
        const unit = Number(product.dataset.base) + (mode === 'ready' ? 0 : Number(product.dataset.custom));
        const quantity = product.elements.quantity;
        for (const option of product.querySelectorAll('input[name="size"]')) {
            const available = variants.some(variant => variant.color === color && variant.size === option.value);
            option.disabled = !available;
            if (!available) option.checked = false;
        }
        const variant = variants.find(variant => variant.color === color && variant.size === size);
        quantity.max = Math.min(10, variant?.stock ?? 10);
        document.querySelector('[data-product-price]').textContent = money.format(unit);
        product.querySelector('[data-line-price]').textContent = money.format(unit * (Number(quantity.value) || 1));
        for (const panel of product.querySelectorAll('[data-design-panel]')) {
            const active = panel.dataset.designPanel === mode;
            panel.hidden = !active;
            const input = panel.querySelector('input, textarea');
            input.disabled = !active;
            input.required = active;
        }
    };
    product.addEventListener('input', render);
    render();
}

const checkout = document.querySelector('[data-checkout]');
if (checkout) {
    const egp = amount => money.format(amount) + ' ج.م.';
    const render = () => {
        const option = checkout.elements.zone.selectedOptions[0];
        const fee = option?.dataset.fee;
        const total = fee === undefined ? null : Number(checkout.dataset.subtotal) + Number(fee);
        const payment = new FormData(checkout).get('payment');
        const deposit = Number(checkout.dataset.deposit);
        checkout.querySelector('[data-shipping]').textContent = fee === undefined ? 'اختار المحافظة' : egp(Number(fee));
        checkout.querySelector('[data-total]').textContent = total === null ? '—' : egp(total);
        checkout.querySelector('[data-now]').textContent = payment === 'transfer' ? (deposit ? egp(deposit) : (total === null ? '—' : egp(total))) : egp(0);
        checkout.querySelector('[data-quoted]').value = total ?? '';
    };
    checkout.addEventListener('change', render);
    render();
}
