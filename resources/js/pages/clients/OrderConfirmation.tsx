
import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppTemplate from '@/components/templates/app-template';

declare global {
    interface Window {
        snap: {
            pay: (
                token: string,
                callbacks?: {
                    onSuccess?: () => void;
                    onPending?: () => void;
                    onError?: () => void;
                    onClose?: () => void;
                }
            ) => void;
        };
    }
}

interface Props {
    transactionId: number;
    status: string;
    paymentMethod: string;
    total: number;
}

const SNAP_SCRIPT_URL =
    'https://app.sandbox.midtrans.com/snap/snap.js';

let snapScriptPromise: Promise<void> | null = null;

function loadSnapScript(): Promise<void> {
    if (window.snap) {
        return Promise.resolve();
    }

    if (snapScriptPromise) {
        return snapScriptPromise;
    }

    snapScriptPromise = new Promise((resolve, reject) => {
        let script = document.querySelector<HTMLScriptElement>(
            `script[src="${SNAP_SCRIPT_URL}"]`
        );

        const handleLoad = () => {
            if (window.snap) {
                resolve();
            } else {
                snapScriptPromise = null;
                reject(new Error('Midtrans Snap gagal dimuat.'));
            }
        };

        const handleError = () => {
            snapScriptPromise = null;
            reject(new Error('Gagal memuat script Midtrans.'));
        };

        if (script) {
            if (window.snap) {
                resolve();
                return;
            }

            script.addEventListener('load', handleLoad, { once: true });
            script.addEventListener('error', handleError, { once: true });
            return;
        }

        script = document.createElement('script');
        script.src = SNAP_SCRIPT_URL;
        script.async = true;
        script.setAttribute(
            'data-client-key',
            import.meta.env.VITE_MIDTRANS_CLIENT_KEY
        );

        script.addEventListener('load', handleLoad, { once: true });
        script.addEventListener('error', handleError, { once: true });

        document.body.appendChild(script);
    });

    return snapScriptPromise;
}

export default function OrderConfirmation({
    transactionId,
    status,
    paymentMethod,
    total,
}: Props) {
    const [isPaying, setIsPaying] = useState(false);
    const [snapReady, setSnapReady] = useState(false);

    // Mencegah request pembayaran ganda.
    const paymentLock = useRef(false);

    // Menandai apakah popup pembayaran sedang aktif.
    const popupActive = useRef(false);

    useEffect(() => {
        let mounted = true;

        loadSnapScript()
            .then(() => {
                if (mounted) {
                    setSnapReady(true);
                }
            })
            .catch((error: unknown) => {
                console.error('Kesalahan memuat Midtrans:', error);
            });

        return () => {
            mounted = false;
        };
    }, []);

    const handlePay = async () => {
        // Jangan membuat transaksi atau membuka popup dua kali.
        if (paymentLock.current || popupActive.current) {
            return;
        }

        paymentLock.current = true;
        setIsPaying(true);

        try {
            const prefix = 'XSRF-TOKEN=';

            const xsrf = decodeURIComponent(
                document.cookie
                    .split('; ')
                    .find((cookie) => cookie.startsWith(prefix))
                    ?.slice(prefix.length) ?? ''
            );

            const response = await fetch(
                `/client/orders/${transactionId}/pay`,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-XSRF-TOKEN': xsrf,
                        Accept: 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error('Gagal membuat pembayaran.');
            }

            const data: { snap_token: string } =
                await response.json();

            if (!data.snap_token) {
                throw new Error('Token pembayaran tidak ditemukan.');
            }

            if (!window.snap) {
                await loadSnapScript();
            }

            if (!window.snap) {
                throw new Error('Midtrans Snap belum siap.');
            }

            popupActive.current = true;

            window.snap.pay(data.snap_token, {
                onSuccess: () => {
                    popupActive.current = false;
                    paymentLock.current = false;
                    setIsPaying(false);

                    // Status resmi tetap ditentukan backend/webhook.
                    router.reload();
                },

                onPending: () => {
                    popupActive.current = false;
                    paymentLock.current = false;
                    setIsPaying(false);

                    router.reload();
                },

                onError: () => {
                    popupActive.current = false;
                    paymentLock.current = false;
                    setIsPaying(false);

                    alert('Pembayaran gagal. Silakan coba lagi.');
                },

                onClose: () => {
                    popupActive.current = false;
                    paymentLock.current = false;
                    setIsPaying(false);
                },
            });
        } catch (error) {
            console.error('Kesalahan pembayaran:', error);

            popupActive.current = false;
            paymentLock.current = false;
            setIsPaying(false);

            alert(
                error instanceof Error
                    ? error.message
                    : 'Terjadi kesalahan saat membuat pembayaran.'
            );
        }
    };

    const currentStatus = status.toLowerCase();
    const currentMethod = paymentMethod.toLowerCase();

    const statusLabels: Record<string, string> = {
        pending: 'Menunggu (Pending)',
        paid: 'Lunas (Paid)',
        settlement: 'Lunas (Settlement)',
        capture: 'Berhasil (Capture)',
        processing: 'Diproses',
        completed: 'Selesai',
        failed: 'Gagal',
        expire: 'Kedaluwarsa',
        expired: 'Kedaluwarsa',
        cancel: 'Dibatalkan',
        cancelled: 'Dibatalkan',
    };

    const isPending = currentStatus === 'pending';

    const needsPayment =
        isPending && currentMethod !== 'cash';

    const isPaid = [
        'paid',
        'settlement',
        'capture',
        'completed',
    ].includes(currentStatus);

    const statusColor = isPaid
        ? 'text-green-600 bg-green-50'
        : isPending
            ? 'text-yellow-600 bg-yellow-50'
            : 'text-red-600 bg-red-50';

    return (
        <AppTemplate>
            <Head title="Pesanan Berhasil" />

            <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
                <div className="bg-white rounded-2xl shadow-lg p-8 max-w-lg w-full text-center">

                    {/* Ikon keberhasilan */}
                    <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                        <span className="text-4xl font-bold text-green-600">
                            ✓
                        </span>
                    </div>

                    {/* Judul */}
                    <h1 className="text-2xl font-bold text-gray-800">
                        Pesanan Berhasil!
                    </h1>

                    <p className="text-gray-500 mt-3 leading-relaxed">
                        Terima kasih telah berbelanja. Pesanan kamu berhasil
                        dibuat dan sedang menunggu proses selanjutnya.
                    </p>

                    {/* Informasi pesanan */}
                    <div className="bg-gray-50 border border-gray-200 rounded-xl p-5 mt-6 text-left">
                        <p className="text-sm text-gray-500">
                            Nomor Pesanan
                        </p>

                        <p className="text-2xl font-bold text-green-600 mt-1">
                            #{transactionId}
                        </p>

                        <div className="flex items-center justify-between gap-3 mt-4">
                            <span className="text-sm text-gray-600">
                                Status Pesanan
                            </span>

                            <span
                                className={`text-sm font-semibold px-3 py-1 rounded-full ${statusColor}`}
                            >
                                {statusLabels[currentStatus] ?? status}
                            </span>
                        </div>

                        <div className="flex items-center justify-between gap-3 mt-4">
                            <span className="text-sm text-gray-600">
                                Metode Pembayaran
                            </span>

                            <span className="text-sm font-medium text-gray-800">
                                {paymentMethod}
                            </span>
                        </div>

                        <div className="flex items-center justify-between gap-3 mt-3">
                            <span className="text-sm text-gray-600">
                                Total Pembayaran
                            </span>

                            <span className="text-sm font-bold text-gray-800">
                                {new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR',
                                    maximumFractionDigits: 0,
                                }).format(total)}
                            </span>
                        </div>
                    </div>

                    {/* Tombol pembayaran */}
                    {needsPayment && (
                        <button
                            type="button"
                            onClick={handlePay}
                            disabled={isPaying || !snapReady}
                            className="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold py-3 px-4 rounded-lg transition-colors mt-6"
                        >
                            {!snapReady
                                ? 'Memuat Pembayaran...'
                                : isPaying
                                    ? 'Memproses Pembayaran...'
                                    : 'Bayar Sekarang'}
                        </button>
                    )}

                    {/* Tombol navigasi */}
                    <div className="flex flex-col gap-3 mt-6">
                        <Link
                            href="/pesanan-saya"
                            className="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors text-center"
                        >
                            Lihat Pesanan Saya
                        </Link>

                        <Link
                            href="/menu"
                            className="w-full border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors text-center"
                        >
                            Kembali ke Menu
                        </Link>
                    </div>
                </div>
            </div>
        </AppTemplate>
    );
}
