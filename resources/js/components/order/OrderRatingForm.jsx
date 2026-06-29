import { useRef, useState } from 'react';
import { Icon } from '@iconify/react';
import { getCsrfToken } from '../../utils/orderPricing';

export default function OrderRatingForm({ orderId, onSuccess }) {
    const [rating, setRating] = useState(0);
    const [hover, setHover] = useState(0);
    const [review, setReview] = useState('');
    const [photos, setPhotos] = useState([]);
    const [previews, setPreviews] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const fileRef = useRef();

    const handlePhotoChange = (e) => {
        const files = Array.from(e.target.files).slice(0, 3);
        setPhotos(files);
        const urls = files.map((f) => URL.createObjectURL(f));
        setPreviews(urls);
    };

    const removePhoto = (idx) => {
        const newPhotos = photos.filter((_, i) => i !== idx);
        const newPreviews = previews.filter((_, i) => i !== idx);
        setPhotos(newPhotos);
        setPreviews(newPreviews);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            const formData = new FormData();
            formData.append('rating', rating);
            formData.append('comment', review);
            photos.forEach((file) => {
                formData.append('photos[]', file);
            });

            const res = await fetch(`/api/orders/${orderId}/rating`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
            });

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Gagal mengirim ulasan.');
            }

            if (onSuccess) {
                onSuccess();
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const STAR_LABELS = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Bagus', 'Sangat Bagus!'];

    return (
        <div className="rounded-2xl border-2 border-dashed border-[#009b97]/30 bg-gradient-to-br from-emerald-50 to-teal-50 p-6">
            <div className="mb-4 flex items-center gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-[#009b97]">
                    <Icon icon="lucide:star" className="w-6 h-6" />
                </div>
                <div>
                    <h3 className="font-bold text-gray-900">Berikan Ulasan</h3>
                    <p className="text-sm text-gray-500">Bagikan pengalaman Anda dengan pesanan ini</p>
                </div>
            </div>

            <form onSubmit={handleSubmit} data-no-loading="true" className="space-y-5">
                {/* Star Rating */}
                <div>
                    <label className="mb-2 block text-sm font-semibold text-gray-700">Rating</label>
                    <div className="flex items-center gap-2">
                        {[1, 2, 3, 4, 5].map((star) => (
                            <button
                                key={star}
                                type="button"
                                onClick={() => setRating(star)}
                                onMouseEnter={() => setHover(star)}
                                onMouseLeave={() => setHover(0)}
                                className="transition-transform hover:scale-110 focus:outline-none"
                            >
                                <Icon 
                                    icon={(hover || rating) >= star ? "material-symbols:star" : "material-symbols:star-outline"}
                                    className={`w-9 h-9 ${(hover || rating) >= star ? 'text-amber-400' : 'text-slate-300'}`}
                                />
                            </button>
                        ))}
                        {(hover || rating) > 0 && (
                            <span className="ml-2 text-sm font-semibold text-[#009b97]">
                                {STAR_LABELS[hover || rating]}
                            </span>
                        )}
                    </div>
                </div>

                {/* Review Text */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700">Komentar (opsional)</label>
                    <textarea
                        rows={3}
                        value={review}
                        onChange={(e) => setReview(e.target.value)}
                        placeholder="Ceritakan pengalaman Anda dengan produk ini..."
                        className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-[#009b97] focus:outline-none focus:ring-2 focus:ring-[#009b97]/20"
                        maxLength={1000}
                    />
                    <p className="mt-1 text-right text-xs text-gray-400">{review.length}/1000</p>
                </div>

                {/* Photo Upload */}
                <div>
                    <label className="mb-2 block text-sm font-medium text-gray-700">Foto (maks. 3 foto)</label>
                    <div className="flex flex-wrap gap-3">
                        {previews.map((url, idx) => (
                            <div key={idx} className="relative">
                                <img
                                    src={url}
                                    alt={`Preview ${idx + 1}`}
                                    className="h-20 w-20 rounded-xl object-cover border border-gray-200 shadow-sm"
                                />
                                <button
                                    type="button"
                                    onClick={() => removePhoto(idx)}
                                    className="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white text-xs shadow hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        ))}
                        {photos.length < 3 && (
                            <button
                                type="button"
                                onClick={() => fileRef.current?.click()}
                                className="flex h-20 w-20 flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white text-gray-400 hover:border-[#009b97] hover:text-[#009b97] transition"
                            >
                                <Icon icon="lucide:camera" className="w-6 h-6" />
                                <span className="text-xs mt-1">Tambah</span>
                            </button>
                        )}
                    </div>
                    <input
                        ref={fileRef}
                        type="file"
                        accept="image/*"
                        multiple
                        className="hidden"
                        onChange={handlePhotoChange}
                    />
                </div>

                {error && (
                    <div className="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {error}
                    </div>
                )}

                <button
                    type="submit"
                    disabled={loading || rating === 0}
                    className="w-full rounded-xl bg-gradient-to-r from-[#009b97] to-[#007a77] py-3 font-bold text-white shadow transition hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {loading ? (
                        <span className="flex items-center justify-center gap-2">
                            <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            Mengirim...
                        </span>
                    ) : (
                        <span className="flex items-center justify-center gap-1.5">
                            <Icon icon="lucide:send" className="w-4 h-4" />
                            Kirim Ulasan
                        </span>
                    )}
                </button>
            </form>
        </div>
    );
}
