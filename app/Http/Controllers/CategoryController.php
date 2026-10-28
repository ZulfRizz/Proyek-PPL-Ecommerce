<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        $search = trim((string) $request->query('search', ''));

        $products = [];

        if ($search !== '') {
            $products = Item::query()
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                })
                ->get();
        }

        return Inertia::render('clients/menu', [
            'categories' => $categories,
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $items = $category->items()
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->get();

        $categories = Category::all();

        return Inertia::render('clients/kategori', [
            'kategori' => $category->name,
            'slug' => $category->slug,
            'produk' => $items,
            'categories' => $categories,
        ]);
    }
}
