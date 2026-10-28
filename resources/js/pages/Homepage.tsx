import Jumbotron from "@/components/layouts/jumbotron";
import Main from "@/components/layouts/main";
import CustomerReview from "@/components/layouts/review";
import AppTemplate from "@/components/templates/app-template";
import { Link } from "@inertiajs/react";

export interface Item {
  id: number;
  name: string;
  description: string | null;
  price: number;
  unit: string;
  stock: number;
  image_url: string;
  discount: number;
  is_available: boolean;
  category?: {
    id: number;
    name: string;
    slug: string;
  };
}

interface HomepageProps {
  items: Item[];
}

const Homepage = ({ items }: HomepageProps) => {
  return (
    <AppTemplate className="bg-[#FFFFFF]">
      <Jumbotron />
      <form className="mx-16 mt-4">
        <input
          type="text"
          placeholder="Search For Menu.."
          className="w-full rounded-full p-3 border"
        />
      </form>
      <nav aria-label="products navigation display">
        <ul className="flex justify-around p-3 mt-5 gap-3 bg-[#F3F3F3] text-2xl font-bold">
          <li className="rounded-full px-5 py-1 text-white bg-black">
            <Link href="#">Kue Basah</Link>
          </li>
          <li className="text-black">
            <Link href="#">Kue Kering</Link>
          </li>
          <li className="text-black">
            <Link href="#">Kue Modern</Link>
          </li>
          <li className="text-black">
            <Link href="#">Gorengan</Link>
          </li>
          <li className="text-black">
            <Link href="#">Minuman</Link>
          </li>
          <li className="text-black">
            <Link href="#">Pudding</Link>
          </li>
        </ul>
      </nav>
      <div id="hero-section" className="mx-16">
        <div id="nav" className="mt-10 flex items-center justify-between text-black">
          <h1 className="text-3xl font-bold">Up to -40%🎊RB Store exclusive deals</h1>
          <div className="flex items-center">
            <Link href="#" className="px-13">Gorengan</Link>
            <Link href="#" className="px-13">Kue Kering</Link>
            <Link href="#" className="rounded-4xl border border-green-300 px-13 py-2">Kue Basah</Link>
            <Link href="#" className="px-13">others</Link>
          </div>
        </div>

        <div className="mt-6 flex gap-2.5 overflow-auto">
          <div className="relative h-60 w-full rounded-2xl bg-[url('/img/dadar-gulung.png')] bg-cover bg-center">
            <h1 className="pt-44 pl-10 text-[#51793E]">Kue Basah</h1>
            <p className="pl-10 text-2xl font-bold text-white">Dadar Gulung</p>
            <div className="absolute top-0 right-4 flex h-14 w-20 items-center justify-center rounded-b-2xl bg-black font-bold text-white">
              -40%
            </div>
          </div>
          <div className="relative h-60 w-full rounded-2xl bg-[url('/img/cucur.png')] bg-cover bg-center">
            <h1 className="pt-44 pl-10 text-[#51793E]">Kue Basah</h1>
            <p className="pl-10 text-2xl font-bold text-white">Kue Cucur</p>
            <div className="absolute top-0 right-4 flex h-14 w-20 items-center justify-center rounded-b-2xl bg-black font-bold text-white">
              -40%
            </div>
          </div>
          <div className="relative h-60 w-full rounded-2xl bg-[url('/img/koci-koci.png')] bg-cover bg-center">
            <h1 className="pt-44 pl-10 text-[#51793E]">Kue Basah</h1>
            <p className="pl-10 text-2xl font-bold text-white">Koci-koci</p>
            <div className="absolute top-0 right-4 flex h-14 w-20 items-center justify-center rounded-b-2xl bg-black font-bold text-white">
              -40%
            </div>
          </div>
        </div>
      </div>

      {/* Menu dari database */}
      <Main items={items} />

      <CustomerReview />
    </AppTemplate>
  );
};

export default Homepage;