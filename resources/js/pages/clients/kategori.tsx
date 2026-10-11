
import KategoriJumbotron from "@/components/layouts/kategori-jumbotron";
import Main from "@/components/layouts/main-menu";
import AppTemplate from "@/components/templates/app-template";
import CustomerReview from "@/components/layouts/review";
import { Link } from "@inertiajs/react";

interface Category {
  id: number;
  name: string;
  slug: string;
}

interface Produk {
  id: number;
  name: string;
  description: string;
  price: number;
  image_url: string;
}

interface Props {
  kategori: string;
  categorySlug: string;
  categories: Category[];
  produk: Produk[];
}

export default function KategoriPage({
  kategori,
  categorySlug,
  categories,
  produk,
}: Props) {
  return (
    <AppTemplate className="bg-white">
      <KategoriJumbotron
        judul={kategori}
        keterangan={`Produk kategori ${kategori}`}
        gambar="/img/default.png"
      />

      <nav aria-label="Kategori produk">
        <ul className="mt-5 flex flex-wrap justify-center gap-3 bg-[#F3F3F3] p-4">
          <li>
            <Link
              href="/menu"
              className="rounded-full px-5 py-2 font-bold text-black transition hover:bg-black hover:text-white"
            >
              Semua Produk
            </Link>
          </li>

          {categories.map((cat) => {
            const aktif = cat.slug === categorySlug;

            return (
              <li key={cat.id}>
                <Link
                  href={`/menu/${cat.slug}`}
                  aria-current={aktif ? "page" : undefined}
                  className={`rounded-full px-5 py-2 font-bold transition ${
                    aktif
                      ? "bg-black text-white"
                      : "text-black hover:bg-black hover:text-white"
                  }`}
                >
                  {cat.name}
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>

      <Main kategori={kategori} produk={produk} />

      <CustomerReview />
    </AppTemplate>
  );
}