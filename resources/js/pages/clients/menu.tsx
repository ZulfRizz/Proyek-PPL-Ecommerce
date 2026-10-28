
import AppTemplate from "@/components/templates/app-template";
import CustomerReview from "@/components/layouts/review";
import { Link, router } from "@inertiajs/react";
import { FormEvent, useState } from "react";

interface Category {
  id: number;
  name: string;
  slug: string;
}

interface Product {
  id: number;
  name: string;
  description: string | null;
  price: number;
  unit: string;
  stock: number;
  image_url: string;
  discount: number;
}

interface MenuProps {
  categories: Category[];
  products?: Product[];
  search?: string;
}

export default function Menu({
  categories,
  products = [],
  search = "",
}: MenuProps) {
  const [keyword, setKeyword] = useState(search);

  function handleSearch(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    router.get(
      "/menu",
      { search: keyword.trim() },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  }

  return (
    <AppTemplate className="bg-white">
      <div className="mx-4 mt-4 md:mx-16">
        <form onSubmit={handleSearch} role="search">
          <label htmlFor="menu-search" className="sr-only">
            Cari produk
          </label>
          <div className="flex overflow-hidden rounded-full border border-gray-400 bg-white focus-within:ring-2 focus-within:ring-green-700">
            <input
              id="menu-search"
              type="search"
              name="search"
              value={keyword}
              onChange={(event) => setKeyword(event.target.value)}
              placeholder="Cari nama kue atau produk..."
              className="min-w-0 flex-1 bg-white px-5 py-3 text-base text-gray-900 placeholder:text-gray-500 outline-none"
            />
            <button
              type="submit"
              className="bg-[#51793E] px-6 py-3 font-semibold text-white hover:bg-green-800"
            >
              Search
            </button>
          </div>
        </form>
      </div>

      <nav aria-label="Navigasi kategori produk">
        <ul className="mt-5 flex flex-wrap justify-around gap-3 bg-[#F3F3F3] p-3 text-lg font-bold md:text-2xl">
          {categories.map((cat) => (
            <li key={cat.id}>
              <Link
                href={`/menu/${cat.slug}`}
                className="inline-block rounded-full px-5 py-1 text-black transition hover:bg-black hover:text-white"
              >
                {cat.name}
              </Link>
            </li>
          ))}
        </ul>
      </nav>

      {search.trim() !== "" && (
        <section className="mx-4 mt-8 md:mx-16">
          <h2 className="mb-4 text-xl font-bold text-gray-900">
            Hasil pencarian: &quot;{search}&quot;
          </h2>

          {products.length === 0 ? (
            <p className="text-gray-700">
              Produk tidak ditemukan. Coba kata kunci lain.
            </p>
          ) : (
            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
              {products.map((product) => (
                <article
                  key={product.id}
                  className="overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                >
                  {product.image_url && (
                      <img
                          src={
                              product.image_url.startsWith("http")
                                  ? product.image_url
                                  : product.image_url.startsWith("/")
                                      ? product.image_url
                                      : `/${product.image_url}`
                          }
                          alt={product.name}
                          className="mb-3 h-40 w-full rounded-lg object-cover"
                          onError={(event) => {
                              console.error(
                                  "Gambar produk gagal dimuat:",
                                  event.currentTarget.src
                              );
                          }}
                      />
                  )}
                  <h3 className="font-bold text-gray-900">
                    {product.name}
                  </h3>
                  {product.description && (
                    <p className="mt-1 text-sm text-gray-600">
                      {product.description}
                    </p>
                  )}
                  <p className="mt-2 font-semibold text-green-800">
                    Rp {Number(product.price).toLocaleString("id-ID")}
                  </p>
                  <p className="text-sm text-gray-600">
                    Stok: {product.stock} {product.unit}
                  </p>
                </article>
              ))}
            </div>
          )}
        </section>
      )}

      <CustomerReview />
    </AppTemplate>
  );
}
