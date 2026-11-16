<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $search = trim((string) $request->query('q', ''));

        if (!array_key_exists((string) $category, Product::CATEGORIES)) {
            $category = null;
        }

        $products = Product::with('vendor')
            ->where('is_available', true)
            ->whereHas('vendor', fn ($q) => $q->where('is_open', true))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('menu', [
            'products' => $products,
            'categories' => Product::CATEGORIES,
            'activeCategory' => $category,
            'search' => $search,
        ]);
    }
}
