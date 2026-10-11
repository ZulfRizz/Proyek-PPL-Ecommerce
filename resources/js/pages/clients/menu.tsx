
import AppTemplate from "@/components/templates/app-template";
import CustomerReview from "@/components/layouts/review";
import Main from "@/components/layouts/main-menu";
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

interface MenuProps {
  categories: Category[];
  produk: Produk[];
}

export default function Menu({ categories, produk }: MenuProps) {
  return (
    <AppTemplate className="bg-white">
      <div className="mx-6 mt-4 md:mx-16">
        <form onSubmit={(e) => e.preventDefault()}>
          <input
            type="text"
            placeholder="Cari menu..."
            className="w-full rounded-full border p-3"
          />
        </form>
      </div>

      <nav aria-label="Kategori produk">
        <ul className="mt-5 flex flex-wrap justify-center gap-3 bg-[#F3F3F3] p-4">
          <li>
            <Link
              href="/menu"
              aria-current="page"
              className="rounded-full bg-black px-5 py-2 font-bold text-white"
            >
              Semua Produk
            </Link>
          </li>

          {categories.map((cat) => (
            <li key={cat.id}>
              <Link
                href={`/menu/${cat.slug}`}
                className="rounded-full px-5 py-2 font-bold text-black transition hover:bg-black hover:text-white"
              >
                {cat.name}
              </Link>
            </li>
          ))}
        </ul>
      </nav>

      <Main kategori="Semua Produk" produk={produk} />

      <CustomerReview />
    </AppTemplate>
  );
}