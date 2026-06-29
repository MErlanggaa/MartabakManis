import { useState, useEffect } from 'react';
import { Icon } from '@iconify/react';
import { motion, AnimatePresence } from 'framer-motion';
import SaldoHistory from './SaldoHistory';

export default function SaldoSection() {
    const [saldo, setSaldo] = useState({
        saldo_tersedia: 0,
        pemasukan_kotor: 0,
        pemasukan_bersih: 0,
        biaya_platform: 0,
        total_wd_approved: 0,
        total_deductions: 0,
        pemasukan_hari_ini: 0
    });
    const [withdraws, setWithdraws] = useState([]);
    const [loading, setLoading] = useState(true);
    
    // Form fields
    const [jumlah, setJumlah] = useState('');
    const [bank, setBank] = useState('BCA');
    const [nomorRekening, setNomorRekening] = useState('');
    const [namaPemilik, setNamaPemilik] = useState('');
    
    const [submitting, setSubmitting] = useState(false);
    const [message, setMessage] = useState(null);
    const [error, setError] = useState(null);

    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    const fetchData = async () => {
        try {
            const [saldoRes, wdRes] = await Promise.all([
                fetch('/umkm/finance/summary'),
                fetch('/umkm/withdraws')
            ]);
            
            const saldoData = await saldoRes.json();
            const wdData = await wdRes.json();
            
            if (saldoData.success) setSaldo(saldoData.summary);
            if (wdData.success) setWithdraws(wdData.withdraws);
        } catch (err) {
            console.error('Error fetching wallet data:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
        // Poll every 10 seconds for real-time updates
        const interval = setInterval(fetchData, 10000);
        return () => clearInterval(interval);
    }, []);

    const handleSubmitWd = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setMessage(null);
        setError(null);

        if (Number(jumlah) > saldo.saldo_tersedia) {
            setError('Saldo tidak mencukupi untuk melakukan penarikan.');
            setSubmitting(false);
            return;
        }

        try {
            const res = await fetch('/umkm/withdraws/request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    jumlah: Number(jumlah),
                    rekening_bank: bank,
                    nomor_rekening: nomorRekening,
                    nama_pemilik: namaPemilik
                })
            });

            const data = await res.json();
            if (data.success) {
                setMessage(data.message);
                setJumlah('');
                setNomorRekening('');
                setNamaPemilik('');
                fetchData(); // reload
            } else {
                setError(data.message || 'Gagal mengajukan penarikan dana.');
            }
        } catch (err) {
            setError('Terjadi kesalahan jaringan. Silakan coba lagi.');
        } finally {
            setSubmitting(false);
        }
    };

    const formatRupiah = (val) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(val);
    };

    if (loading) {
        return (
            <div className="flex h-32 items-center justify-center rounded-2xl border border-slate-100 bg-white">
                <div className="h-8 w-8 animate-spin rounded-full border-4 border-[#009b97] border-t-transparent" />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Stats Cards */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {/* Saldo Tersedia */}
                <div className="group relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-lg">
                    <div className="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10 group-hover:scale-150 transition-transform duration-500" />
                    <p className="text-sm font-semibold text-emerald-100 uppercase tracking-wider">Saldo Tersedia (WD)</p>
                    <p className="mt-3 text-2xl font-black tracking-tight md:text-3xl">{formatRupiah(saldo.saldo_tersedia)}</p>
                    <div className="mt-4 flex items-center gap-1.5 text-xs text-emerald-100">
                        <Icon icon="lucide:check-circle" className="h-4 w-4" />
                        <span>Dapat ditarik ke rekening</span>
                    </div>
                </div>

                {/* Pemasukan Hari Ini */}
                <div className="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-card transition-all hover:-translate-y-1">
                    <div className="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-slate-50 group-hover:scale-150 transition-transform duration-500" />
                    <p className="text-sm font-bold text-slate-500 uppercase tracking-wider">Pemasukan Hari Ini</p>
                    <p className="mt-3 text-2xl font-black text-slate-900 tracking-tight md:text-3xl">{formatRupiah(saldo.pemasukan_hari_ini)}</p>
                    <div className="mt-4 flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                        <Icon icon="lucide:trending-up" className="h-4 w-4" />
                        <span>Order online hari ini</span>
                    </div>
                </div>

                {/* Total Pemasukan Online */}
                <div className="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-card transition-all hover:-translate-y-1">
                    <div className="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-slate-50 group-hover:scale-150 transition-transform duration-500" />
                    <p className="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Pendapatan (Bersih & Kotor)</p>
                    <p className="mt-3 text-lg font-black text-slate-900 tracking-tight">Bersih: <span className="text-[#009b97]">{formatRupiah(saldo.pemasukan_bersih)}</span></p>
                    <p className="text-xs text-slate-400 font-semibold mt-1">Kotor: {formatRupiah(saldo.pemasukan_kotor)}</p>
                    <div className="mt-4 flex items-center gap-1.5 text-xs text-blue-600 font-semibold">
                        <Icon icon="lucide:globe" className="h-4 w-4" />
                        <span>Biaya Platform: {formatRupiah(saldo.biaya_platform)}</span>
                    </div>
                </div>

                {/* Total Withdraw */}
                <div className="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-card transition-all hover:-translate-y-1">
                    <div className="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-slate-50 group-hover:scale-150 transition-transform duration-500" />
                    <p className="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Ditarik (WD)</p>
                    <p className="mt-3 text-2xl font-black text-slate-900 tracking-tight md:text-3xl">{formatRupiah(saldo.total_wd_approved)}</p>
                    {saldo.total_deductions > 0 && (
                        <p className="text-xs text-rose-600 font-bold mt-1">Sanksi Potongan: {formatRupiah(saldo.total_deductions)}</p>
                    )}
                    <div className="mt-4 flex items-center gap-1.5 text-xs text-purple-600 font-semibold">
                        <Icon icon="lucide:arrow-up-right" className="h-4 w-4" />
                        <span>Telah ditransfer ke rekening</span>
                    </div>
                </div>
            </div>

            {/* Withdraw Section */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Form WD */}
                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-card">
                    <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2 mb-4">
                        <Icon icon="lucide:wallet" className="w-5 h-5 text-[#009b97]" />
                        Tarik Saldo Online
                    </h3>

                    {message && (
                        <div className="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800 flex items-center gap-2">
                            <Icon icon="lucide:check-circle" className="w-4 h-4 text-emerald-600 flex-shrink-0" />
                            <span>{message}</span>
                        </div>
                    )}

                    {error && (
                        <div className="mb-4 rounded-xl bg-red-50 border border-red-200 p-3 text-sm text-red-800 flex items-center gap-2">
                            <Icon icon="lucide:alert-circle" className="w-4 h-4 text-red-600 flex-shrink-0" />
                            <span>{error}</span>
                        </div>
                    )}

                    <form onSubmit={handleSubmitWd} className="space-y-4" data-no-loading="true">
                        <div>
                            <label className="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Penarikan (Min. Rp 50.000)</label>
                            <div className="relative">
                                <span className="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">Rp</span>
                                <input
                                    type="number"
                                    required
                                    min="50000"
                                    placeholder="50000"
                                    value={jumlah}
                                    onChange={(e) => setJumlah(e.target.value)}
                                    className="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/15 text-sm font-bold text-slate-800"
                                />
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-semibold text-slate-700 mb-1.5">Bank Tujuan</label>
                            <select
                                value={bank}
                                onChange={(e) => setBank(e.target.value)}
                                className="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:border-[#009b97] focus:outline-none text-sm text-slate-800 font-semibold bg-white"
                            >
                                <option value="BCA">BCA (Bank Central Asia)</option>
                                <option value="MANDIRI">Mandiri</option>
                                <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                                <option value="BNI">BNI (Bank Negara Indonesia)</option>
                                <option value="GOPAY">GoPay</option>
                                <option value="OVO">OVO</option>
                                <option value="DANA">DANA</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Rekening / E-Wallet</label>
                            <input
                                type="text"
                                required
                                placeholder="1234567890"
                                value={nomorRekening}
                                onChange={(e) => setNomorRekening(e.target.value)}
                                className="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/15 text-sm text-slate-800"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-semibold text-slate-700 mb-1.5">Nama Pemilik Rekening</label>
                            <input
                                type="text"
                                required
                                placeholder="Nama sesuai buku tabungan"
                                value={namaPemilik}
                                onChange={(e) => setNamaPemilik(e.target.value)}
                                className="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/15 text-sm text-slate-800"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={submitting || !jumlah || saldo.saldo_tersedia < 50000}
                            className="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 py-3 font-bold text-white shadow transition hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {submitting ? (
                                <span className="flex items-center justify-center gap-2">
                                    <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                    Memproses...
                                </span>
                            ) : (
                                'Ajukan Penarikan'
                            )}
                        </button>
                    </form>
                </div>

                {/* History WD */}
                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-card lg:col-span-2 flex flex-col">
                    <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2 mb-4">
                        <Icon icon="lucide:history" className="w-5 h-5 text-purple-500" />
                        Riwayat Penarikan Dana
                    </h3>

                    <div className="flex-1 overflow-x-auto">
                        {withdraws.length === 0 ? (
                            <div className="flex flex-col items-center justify-center h-full min-h-[250px] text-slate-400">
                                <Icon icon="lucide:wallet-cards" className="w-12 h-12 text-slate-200 mb-2" />
                                <p className="text-sm">Belum ada riwayat penarikan dana.</p>
                            </div>
                        ) : (
                            <table className="min-w-full divide-y divide-slate-100 text-sm">
                                <thead className="bg-slate-50/50">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                                        <th className="px-4 py-3 text-left font-semibold text-slate-600">Jumlah</th>
                                        <th className="px-4 py-3 text-left font-semibold text-slate-600">Rekening</th>
                                        <th className="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                                        <th className="px-4 py-3 text-left font-semibold text-slate-600">Keterangan / Bukti</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {withdraws.map((wd) => (
                                        <tr key={wd.id} className="hover:bg-slate-50/50">
                                            <td className="px-4 py-3 whitespace-nowrap text-slate-500">{wd.created_at}</td>
                                            <td className="px-4 py-3 whitespace-nowrap font-bold text-slate-800">{formatRupiah(wd.jumlah)}</td>
                                            <td className="px-4 py-3">
                                                <span className="font-semibold text-slate-700">{wd.rekening_bank}</span>
                                                <div className="text-xs text-slate-400 font-mono">{wd.nomor_rekening} a.n {wd.nama_pemilik}</div>
                                            </td>
                                            <td className="px-4 py-3 text-center whitespace-nowrap">
                                                <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold ${
                                                    wd.status === 'approved' ? 'bg-emerald-50 text-emerald-700' :
                                                    wd.status === 'rejected' ? 'bg-rose-50 text-rose-700' :
                                                    'bg-amber-50 text-amber-700'
                                                }`}>
                                                    <span className={`h-1.5 w-1.5 rounded-full ${
                                                        wd.status === 'approved' ? 'bg-emerald-500' :
                                                        wd.status === 'rejected' ? 'bg-rose-500' :
                                                        'bg-amber-500'
                                                    }`} />
                                                    {wd.status_label}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-slate-600">
                                                {wd.admin_note && <div className="text-xs italic bg-slate-50 border-l-2 border-slate-300 p-1.5 rounded mb-1">{wd.admin_note}</div>}
                                                {wd.bukti_transfer ? (
                                                    <a
                                                        href={wd.bukti_transfer}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-flex items-center gap-1 text-xs font-semibold text-[#009b97] hover:underline"
                                                    >
                                                        <Icon icon="lucide:image" className="w-3.5 h-3.5" />
                                                        Lihat Bukti TF
                                                    </a>
                                                ) : wd.status === 'approved' ? (
                                                    <span className="text-xs text-slate-400 italic">Tanpa bukti TF</span>
                                                ) : (
                                                    '-'
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>
            </div>

            {/* Riwayat Mutasi Keuangan */}
            <div className="mt-8">
                <SaldoHistory />
            </div>
        </div>
    );
}
