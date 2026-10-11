<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Inertia\Inertia;
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        $produk = Item::where('is_available', true)->get();

        return Inertia::render('clients/menu', [
            'categories' => $categories,
            'produk' => $produk,
        ]);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $categories = Category::orderBy('name')->get();

        $produk = $category->items()
            ->where('is_available', true)
            ->get();

        return Inertia::render('clients/kategori', [
            'kategori' => $category->name,
            'categorySlug' => $category->slug,
            'categories' => $categories,
            'produk' => $produk,
        ]);
    }
}