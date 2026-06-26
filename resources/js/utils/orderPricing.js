export const APP_TAX_RATE = 0.02;

export const DELIVERY_OPTIONS = [
    {
        id: 'gojek',
        label: 'Gojek',
        fee: 15000,
        color: '#00AA13',
        icon: '🛵',
        eta: '15-25 menit',
    },
    {
        id: 'grab',
        label: 'Grab',
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

export function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

export function calculatePricing(unitPrice, quantity, deliveryMethod) {
    const subtotal = unitPrice * quantity;
    const delivery = DELIVERY_OPTIONS.find((d) => d.id === deliveryMethod);
    const deliveryFee = delivery?.fee ?? 0;
    const appTax = Math.round(subtotal * APP_TAX_RATE);
    const total = subtotal + deliveryFee + appTax;

    return { subtotal, deliveryFee, appTax, total };
}

export function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}
