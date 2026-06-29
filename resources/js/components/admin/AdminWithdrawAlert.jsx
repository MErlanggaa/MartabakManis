import { useState, useEffect, useRef } from 'react';
import { Icon } from '@iconify/react';
import { motion, AnimatePresence } from 'framer-motion';

export default function AdminWithdrawAlert() {
    const [withdraws, setWithdraws] = useState([]);
    const [showAlert, setShowAlert] = useState(false);
    const [alertWd, setAlertWd] = useState(null);
    const knownIds = useRef(new Set());
    const isFirstLoad = useRef(true);
    const lastReportCount = useRef(0);

    // Processing modal state
    const [activeWd, setActiveWd] = useState(null);
    const [actionType, setActionType] = useState('approve'); // approve or reject
    const [adminNote, setAdminNote] = useState('');
    const [buktiFile, setBuktiFile] = useState(null);
    const [buktiPreview, setBuktiPreview] = useState(null);
    const [submitting, setSubmitting] = useState(false);
    const [modalError, setModalError] = useState('');

    const fileInputRef = useRef();

    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    const playNotificationSound = () => {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 660; // Slightly lower frequency for WD alert
            gain.gain.value = 0.15;
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch {
            /* ignore audio issues */
        }
    };

    const fetchWithdraws = async () => {
        try {
            const res = await fetch('/admin/withdraws');
            const data = await res.json();
            if (data.success) {
                const list = data.withdraws || [];
                setWithdraws(list);

                // Check for new pending withdraws
                const pendings = list.filter(w => w.status === 'pending');
                for (const wd of pendings) {
                    if (!knownIds.current.has(wd.id)) {
                        knownIds.current.add(wd.id);
                        if (!isFirstLoad.current) {
                            setAlertWd(wd);
                            setShowAlert(true);
                            playNotificationSound();
                        }
                    }
                }
                
                // Add non-pendings to known so we don't alert them later
                list.forEach(w => {
                    if (w.status !== 'pending') {
                        knownIds.current.add(w.id);
                    }
                });
            }
        } catch (err) {
            console.error('Error fetching withdraw requests:', err);
        }
    };

    const fetchReportsCount = async () => {
        try {
            const res = await fetch('/admin/laporan/pending-count');
            const data = await res.json();
            if (data.success) {
                const count = data.count || 0;
                if (count > lastReportCount.current) {
                    if (!isFirstLoad.current) {
                        playNotificationSound();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Laporan Baru Masuk!',
                                text: 'Ada aduan/komplain baru dari pelanggan yang memerlukan mediasi.',
                                icon: 'warning',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                            });
                        }
                    }
                }
                lastReportCount.current = count;
            }
        } catch (err) {
            console.error('Error fetching reports count:', err);
        }
    };

    useEffect(() => {
        const initData = async () => {
            await fetchWithdraws();
            await fetchReportsCount();
            isFirstLoad.current = false;
        };
        initData();
        
        // Panggil mark-seen ketika WD alert ditampilkan atau admin melihat komponennya
        const markSeenTimeout = setTimeout(() => {
            fetch('/admin/withdraws/mark-seen', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            });
        }, 1500);

        const interval = setInterval(() => {
            fetchWithdraws();
            fetchReportsCount();
        }, 8000);
        
        return () => {
            clearTimeout(markSeenTimeout);
            clearInterval(interval);
        };
    }, []);

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setBuktiFile(file);
            setBuktiPreview(URL.createObjectURL(file));
        }
    };

    const handleProcessWd = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setModalError('');

        try {
            const formData = new FormData();
            formData.append('action', actionType);
            if (adminNote) formData.append('admin_note', adminNote);
            if (buktiFile) formData.append('bukti', buktiFile);

            const res = await fetch(`/admin/withdraws/${activeWd.id}/process`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                // reset state
                setActiveWd(null);
                setAdminNote('');
                setBuktiFile(null);
                setBuktiPreview(null);
                fetchWithdraws();
            } else {
                setModalError(data.message || 'Gagal memproses penarikan.');
            }
        } catch (err) {
            setModalError('Terjadi kesalahan jaringan.');
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

    return (
        <div className="space-y-6">
            {/* Pop-up Alert Banner */}
            <AnimatePresence>
                {showAlert && alertWd && (
                    <motion.div
                        initial={{ opacity: 0, y: -50, scale: 0.9 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: -20, scale: 0.9 }}
                        className="fixed inset-x-4 top-4 z-50 mx-auto max-w-lg rounded-2xl border-2 border-amber-500 bg-amber-50 p-4 shadow-2xl flex items-start gap-3"
                    >
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white animate-bounce flex-shrink-0">
                            <Icon icon="lucide:bell-ring" className="h-5 w-5" />
                        </div>
                        <div className="flex-1">
                            <h4 className="font-bold text-amber-900 text-sm">Permintaan Penarikan Baru!</h4>
                            <p className="text-amber-800 text-xs mt-1">
                                UMKM <strong>{alertWd.umkm_name}</strong> ingin menarik <strong>{formatRupiah(alertWd.jumlah)}</strong> ke bank <strong>{alertWd.rekening_bank}</strong>.
                            </p>
                            <div className="mt-3 flex gap-2">
                                <button
                                    onClick={() => {
                                        setActiveWd(alertWd);
                                        setActionType('approve');
                                        setShowAlert(false);
                                    }}
                                    className="rounded-lg bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 text-xs font-bold transition shadow-sm"
                                >
                                    Proses Sekarang
                                </button>
                                <button
                                    onClick={() => setShowAlert(false)}
                                    className="rounded-lg border border-amber-300 text-amber-800 px-3 py-1.5 text-xs font-semibold hover:bg-amber-100 transition"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>

            {/* List Table container */}
            <div className="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-100">
                <div className="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 className="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <Icon icon="lucide:dollar-sign" className="w-5 h-5 text-emerald-500" />
                        Daftar Request Withdrawal (Penarikan Saldo)
                    </h2>
                    {withdraws.filter(w => w.status === 'pending').length > 0 && (
                        <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">
                            {withdraws.filter(w => w.status === 'pending').length} Pending
                        </span>
                    )}
                </div>

                <div className="overflow-x-auto">
                    {withdraws.length === 0 ? (
                        <div className="p-12 text-center text-slate-400">
                            <Icon icon="lucide:wallet-cards" className="w-12 h-12 text-slate-200 mx-auto mb-2" />
                            <p className="text-sm">Belum ada request penarikan dana.</p>
                        </div>
                    ) : (
                        <table className="min-w-full divide-y divide-gray-200 text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left font-semibold text-slate-600">UMKM / Pemilik</th>
                                    <th className="px-6 py-3 text-left font-semibold text-slate-600">Jumlah</th>
                                    <th className="px-6 py-3 text-left font-semibold text-slate-600">Tujuan Rekening</th>
                                    <th className="px-6 py-3 text-center font-semibold text-slate-600">Status</th>
                                    <th className="px-6 py-3 text-left font-semibold text-slate-600">Tanggal Pengajuan</th>
                                    <th className="px-6 py-3 text-center font-semibold text-slate-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {withdraws.map((wd) => (
                                    <tr key={wd.id} className="hover:bg-gray-50/50">
                                        <td className="px-6 py-4">
                                            <div className="font-semibold text-slate-900">{wd.umkm_name}</div>
                                            <div className="text-xs text-slate-500">Owner: {wd.umkm_user}</div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                            {formatRupiah(wd.jumlah)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded text-xs">{wd.rekening_bank}</span>
                                            <div className="text-xs text-slate-600 font-mono mt-1">{wd.nomor_rekening}</div>
                                            <div className="text-xs text-slate-500">a.n {wd.nama_pemilik}</div>
                                        </td>
                                        <td className="px-6 py-4 text-center whitespace-nowrap">
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
                                                {wd.status === 'approved' ? 'Disetujui' : wd.status === 'rejected' ? 'Ditolak' : 'Menunggu'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-slate-500">
                                            {wd.created_at}
                                        </td>
                                        <td className="px-6 py-4 text-center whitespace-nowrap">
                                            {wd.status === 'pending' ? (
                                                <button
                                                    onClick={() => {
                                                        setActiveWd(wd);
                                                        setActionType('approve');
                                                    }}
                                                    className="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-xl text-xs font-bold shadow-sm transition"
                                                >
                                                    <Icon icon="lucide:clipboard-check" className="w-3.5 h-3.5" />
                                                    Proses
                                                </button>
                                            ) : (
                                                <div className="text-xs text-slate-500">
                                                    {wd.processed_at}
                                                    {wd.bukti_transfer && (
                                                        <a
                                                            href={wd.bukti_transfer}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="block text-[#009b97] hover:underline font-semibold mt-0.5"
                                                        >
                                                            Lihat Bukti TF
                                                        </a>
                                                    )}
                                                </div>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>

            {/* Modal for processing withdrawal */}
            <AnimatePresence>
                {activeWd && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                        <motion.div
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.95 }}
                            className="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-100"
                        >
                            <div className="border-b border-slate-100 px-6 py-4 flex justify-between items-center">
                                <h3 className="text-base font-bold text-slate-900">Proses Penarikan Dana</h3>
                                <button onClick={() => setActiveWd(null)} className="text-slate-400 hover:text-slate-600">
                                    <Icon icon="lucide:x" className="w-5 h-5" />
                                </button>
                            </div>

                            <form onSubmit={handleProcessWd} className="p-6 space-y-4" data-no-loading="true">
                                <div className="bg-slate-50 p-4 rounded-xl space-y-2 border border-slate-100 text-xs text-slate-600">
                                    <div className="flex justify-between">
                                        <span>Nama UMKM:</span>
                                        <strong className="text-slate-800">{activeWd.umkm_name}</strong>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Owner:</span>
                                        <strong className="text-slate-800">{activeWd.umkm_user}</strong>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Tujuan Transfer:</span>
                                        <strong className="text-slate-800">{activeWd.rekening_bank} - {activeWd.nomor_rekening}</strong>
                                    </div>
                                    <div className="flex justify-between">
                                        <span>Atas Nama:</span>
                                        <strong className="text-slate-800">{activeWd.nama_pemilik}</strong>
                                    </div>
                                    <div className="flex justify-between border-t border-slate-200/60 pt-2 text-sm">
                                        <span className="font-bold text-slate-800">Jumlah Penarikan:</span>
                                        <strong className="text-emerald-600 font-extrabold">{formatRupiah(activeWd.jumlah)}</strong>
                                    </div>
                                </div>

                                {modalError && (
                                    <div className="rounded-xl bg-red-50 border border-red-200 p-3 text-xs text-red-800">
                                        {modalError}
                                    </div>
                                )}

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tindakan</label>
                                    <div className="grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            onClick={() => setActionType('approve')}
                                            className={`flex items-center justify-center gap-1.5 py-2.5 rounded-xl border font-bold text-sm transition ${
                                                actionType === 'approve'
                                                    ? 'bg-emerald-50 border-emerald-500 text-emerald-700'
                                                    : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                                            }`}
                                        >
                                            <Icon icon="lucide:check" className="w-4 h-4" />
                                            Setujui & Cairkan
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setActionType('reject')}
                                            className={`flex items-center justify-center gap-1.5 py-2.5 rounded-xl border font-bold text-sm transition ${
                                                actionType === 'reject'
                                                    ? 'bg-rose-50 border-rose-500 text-rose-700'
                                                    : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                                            }`}
                                        >
                                            <Icon icon="lucide:x" className="w-4 h-4" />
                                            Tolak Request
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Admin (Opsional)</label>
                                    <textarea
                                        rows={2}
                                        placeholder="Tambahkan info / alasan tolak / info rekening..."
                                        value={adminNote}
                                        onChange={(e) => setAdminNote(e.target.value)}
                                        className="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-brand-500 focus:outline-none"
                                    />
                                </div>

                                {actionType === 'approve' && (
                                    <div>
                                        <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Upload Bukti Transfer</label>
                                        <div className="flex items-center gap-3">
                                            {buktiPreview ? (
                                                <div className="relative h-16 w-16 rounded-xl overflow-hidden border border-slate-200 flex-shrink-0">
                                                    <img src={buktiPreview} className="h-full w-full object-cover" />
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setBuktiFile(null);
                                                            setBuktiPreview(null);
                                                        }}
                                                        className="absolute right-0.5 top-0.5 bg-red-500 text-white rounded-full p-0.5"
                                                    >
                                                        <Icon icon="lucide:x" className="w-3 h-3" />
                                                    </button>
                                                </div>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() => fileInputRef.current?.click()}
                                                    className="flex h-16 w-16 flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-xl hover:border-emerald-500 text-slate-400 hover:text-emerald-500 transition flex-shrink-0"
                                                >
                                                    <Icon icon="lucide:camera" className="w-5 h-5" />
                                                    <span className="text-[10px] mt-0.5">Upload</span>
                                                </button>
                                            )}
                                            <input
                                                ref={fileInputRef}
                                                type="file"
                                                accept="image/*"
                                                className="hidden"
                                                onChange={handleFileChange}
                                            />
                                            <div className="text-xs text-slate-400">
                                                Unggah bukti transfer dari ATM / Mobile Banking (Max 5MB)
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="border-t border-slate-100 pt-4 flex justify-end gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setActiveWd(null)}
                                        className="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={submitting}
                                        className={`px-4 py-2 rounded-xl text-sm font-bold text-white shadow transition ${
                                            actionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'
                                        }`}
                                    >
                                        {submitting ? 'Memproses...' : 'Kirim'}
                                    </button>
                                </div>
                            </form>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </div>
    );
}
