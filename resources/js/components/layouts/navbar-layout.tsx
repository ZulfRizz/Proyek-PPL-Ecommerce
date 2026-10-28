import FigureCap from '../fragments/figcap';
import NavbarTemplate from '../navbar-template';
import { Link, router, usePage } from "@inertiajs/react";
import { useState, useEffect } from 'react';

// Tipe user
type User = {
  id: number;
  username: string;
  email: string;
};

// Props untuk komponen ini
type NavbarLayoutProps = {
  className?: string;
  user?: User | null;
};

export default function NavbarLayout({ className = "", user: initialUser = null }: NavbarLayoutProps) {
  const { url } = usePage();
  const [activePage, setActivePage] = useState("");
  const [user, setUser] = useState<User | null>(null);

  useEffect(() => {
    setActivePage(url);
  }, [url]);

  useEffect(() => {
    console.log("👤 User dari props:", initialUser);
    setUser(initialUser);
  }, [initialUser]);

  const handleLogout = () => {
    router.post('/logout');
  };

  return (
    <section aria-label="navigation" className={`flex justify-between items-center ${className}`}>
      <FigureCap src="RB-Store1.png" alt="" className="w-72" />

      <div className="flex items-center gap-6">
        <NavbarTemplate />
          {/* guna activatepage untuk ketika berada dihalaman yang sesuai navbar tombol otomatis ganti backgroundnya */}
        <div className="flex gap-4">
            <Link
              href="/Homepage" 
              className={`flex items-center rounded-full px-6 py-1 font-medium transition-colors duration-200 ${
                activePage === "/Homepage"
                  ? "bg-[#028643] text-white"
                  : "bg-gray-200 text-black hover:bg-[#028643] hover:text-white"
              }`}
            >
              Beranda
            </Link>

            <Link
              href="/menu"
              className={`flex items-center rounded-full px-8 py-3 font-medium transition-colors duration-200 ${
                activePage.startsWith("/Menu")
                  ? "bg-[#028643] text-white"
                  : "bg-gray-200 text-black hover:bg-[#028643] hover:text-white"
              }`}
            >
              Menu
            </Link>

            <Link
              href="/pesanan-saya"
              className={`flex items-center rounded-full px-6 py-1 font-medium transition-colors duration-200 ${
                activePage.startsWith("/pesanan")
                  ? "bg-[#028643] text-white"
                  : "bg-gray-200 text-black hover:bg-[#028643] hover:text-white"
              }`}
            >
              Pesanan
            </Link>
          
        </div>

        <div className="flex items-center rounded-4xl bg-black px-6 py-3 text-white ml-4">
          {!user ? (
            <>
              <img src="/svg/male.svg" alt="user" className="pr-2 h-6 w-6" />
              <Link href="/login" className="hover:underline">Login</Link>
              <span className="px-1">/</span>
              <Link href="/register" className="hover:underline">Register</Link>
            </>
          ) : (
            <>
              <img src="/svg/male.svg" alt="user" className="pr-2 h-6 w-6" />
              <Link href={route('profile.show')} className="hover:underline"> {/* Diubah */}
                Halo, {user.username}
              </Link>
              <Link href={route('logout')} method="post" as="button" className="ml-4 underline hover:text-gray-300 transition">Logout</Link>
            </>
          )}
        </div>
      </div>
    </section>
  );
}
