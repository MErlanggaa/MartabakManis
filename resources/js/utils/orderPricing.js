export const APP_TAX_RATE = 0.02;
export const QRIS_TAX = 1000;

export const DELIVERY_OPTIONS = [
    {
        id: 'gojek',
        label: 'Gojek (GoSend)',
        fee: 15000,
        color: '#00AA13',
        icon: '🛵',
        eta: '15-25 menit',
    },
    {
        id: 'grab',
        label: 'Grab (GrabSend)',
        fee: 14000,
        color: '#00B14F',
        icon: '🚗',
        eta: '20-30 menit',
    },
    {
        id: 'umkm_go',
        label: 'UMKM.go',
        fee: 10000,
        color: '#009b97',
        icon: '📦',
        eta: '30-45 menit',
    },
];

export const PAYMENT_OPTIONS = [
    {
        id: 'midtrans',
        label: 'Midtrans (DANA, OVO, Transfer)',
        icon: '💳',
        description: 'Bayar via berbagai metode pembayaran digital',
        extraTax: 0,
    },
    {
        id: 'qris',
        label: 'QRIS',
        icon: '📱',
        description: 'Scan QR Code — kena pajak Rp 1.000',
        extraTax: QRIS_TAX,
    },
];

export function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

export function calculatePricing(unitPrice, quantity, deliveryMethod, paymentMethod = 'midtrans') {
    const subtotal = unitPrice * quantity;
    const delivery = DELIVERY_OPTIONS.find((d) => d.id === deliveryMethod);
    const deliveryFee = delivery?.fee ?? 0;
    const appTax = Math.round(subtotal * APP_TAX_RATE);
    const qrisTax = paymentMethod === 'qris' ? QRIS_TAX : 0;
    const total = subtotal + deliveryFee + appTax + qrisTax;

    return { subtotal, deliveryFee, appTax, qrisTax, total };
}

export function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}
