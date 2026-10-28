
import { Head, Link } from "@inertiajs/react";
import AppTemplate from "@/components/templates/app-template";

interface Props {
    transactionId: number;
}

export default function OrderConfirmation({ transactionId }: Props) {
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

                        <div className="flex items-center justify-between mt-4">
                            <span className="text-sm text-gray-600">
                                Status Pesanan
                            </span>

                            <span className="text-sm font-semibold text-yellow-600 bg-yellow-50 px-3 py-1 rounded-full">
                                Menunggu (Pending)
                            </span>
                        </div>
                    </div>

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
