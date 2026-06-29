import { useState, useEffect } from 'react';
import { Icon } from '@iconify/react';

export default function SaldoHistory() {
    const [mutations, setMutations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [category, setCategory] = useState('');
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);

    const fetchHistory = async () => {
        setLoading(true);
        try {
            const res = await fetch(`/umkm/finance/history?page=${page}&category=${category}`);
            const data = await res.json();
            if (data.success) {
                setMutations(data.mutations || []);
                setLastPage(data.last_page || 1);
            }
        } catch (err) {
            console.error('Error fetching finance mutations:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchHistory();
    }, [page, category]);

    const formatRupiah = (val) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(val);
    };

    return (
        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-card space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <Icon icon="lucide:arrow-left-right" className="w-5 h-5 text-[#009b97]" />
                    Riwayat Keuangan & Aliran Uang
                </h3>
                
                {/* Filter */}
                <div className="flex gap-2">
                    <select
                        value={category}
                        onChange={(e) => { setCategory(e.target.value); setPage(1); }}
                        className="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none"
                    >
                        <option value="">Semua Aliran</option>
                        <option value="order_income">Pemasukan Order</option>
                        <option value="withdrawal">Penarikan Dana (WD)</option>
                        <option value="admin_deduction">Pemotongan Sanksi Admin</option>
                        <option value="refund">Refund</option>
                    </select>
                </div>
            </div>

            {loading ? (
                <div className="flex h-32 items-center justify-center">
                    <div className="h-8 w-8 animate-spin rounded-full border-4 border-[#009b97] border-t-transparent" />
                </div>
            ) : mutations.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-12 text-slate-400">
                    <Icon icon="lucide:database" className="w-12 h-12 text-slate-200 mb-2" />
                    <p className="text-sm">Belum ada catatan mutasi keuangan.</p>
                </div>
            ) : (
                <div className="space-y-4">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50/50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-600">Kategori</th>
                                    <th className="px-4 py-3 text-left font-semibold text-slate-600">Deskripsi</th>
                                    <th className="px-4 py-3 text-right font-semibold text-slate-600">Nominal</th>
                                    <th className="px-4 py-3 text-right font-semibold text-slate-600">Saldo Akhir</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {mutations.map((mut) => (
                                    <tr key={mut.id} className="hover:bg-slate-50/50">
                                        <td className="px-4 py-3 whitespace-nowrap text-slate-500">{mut.date}</td>
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold ${
                                                mut.category === 'order_income' ? 'bg-emerald-50 text-emerald-700' :
                                                mut.category === 'withdrawal' ? 'bg-purple-50 text-purple-700' :
                                                mut.category === 'admin_deduction' ? 'bg-rose-50 text-rose-700' :
                                                'bg-slate-50 text-slate-700'
                                            }`}>
                                                {mut.category_label}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-slate-700 font-medium">
                                            {mut.description}
                                            {mut.order_code && (
                                                <span className="ml-1.5 font-mono text-xs bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold">
                                                    {mut.order_code}
                                                </span>
                                            )}
                                        </td>
                                        <td className={`px-4 py-3 whitespace-nowrap text-right font-bold ${
                                            mut.type === 'credit' ? 'text-emerald-600' : 'text-rose-600'
                                        }`}>
                                            {mut.type === 'credit' ? '+' : '-'} {formatRupiah(mut.amount)}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap text-right font-semibold text-slate-900">
                                            {formatRupiah(mut.balance_after)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {lastPage > 1 && (
                        <div className="flex items-center justify-between border-t border-slate-100 pt-4">
                            <button
                                disabled={page === 1}
                                onClick={() => setPage(prev => Math.max(1, prev - 1))}
                                className="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                            >
                                <Icon icon="lucide:chevron-left" className="w-4 h-4" />
                                Sebelum
                            </button>
                            <span className="text-xs text-slate-500 font-semibold">Halaman {page} dari {lastPage}</span>
                            <button
                                disabled={page === lastPage}
                                onClick={() => setPage(prev => Math.min(lastPage, prev + 1))}
                                className="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                            >
                                Selanjutnya
                                <Icon icon="lucide:chevron-right" className="w-4 h-4" />
                            </button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
