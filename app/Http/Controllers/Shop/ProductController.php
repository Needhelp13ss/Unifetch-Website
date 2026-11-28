<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** The seller manages only their own products. */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        return view('shop.products', [
            'vendor' => $vendor,
            'products' => $vendor->products()->orderBy('category')->orderBy('name')->get(),
            'categories' => Product::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $request->user()->vendor->products()->create($this->data($request) + ['is_available' => true, 'bg_color' => '#FDE7C8']);

        return back()->with('success', 'Product added.');
    }

    public function edit(Request $request, Product $product)
    {
        $this->own($request, $product);

        return view('shop.product', ['product' => $product, 'categories' => Product::CATEGORIES]);
    }

    public function update(Request $request, Product $product)
    {
        $this->own($request, $product);
        $product->update($this->data($request) + ['is_available' => $request->boolean('is_available')]);

        return redirect()->route('shop.products')->with('success', 'Product saved.');
    }

    public function toggle(Request $request, Product $product)
    {
        $this->own($request, $product);
        $product->update(['is_available' => !$product->is_available]);

        return back()->with('success', $product->name . ($product->is_available ? ' is available again.' : ' is now hidden from the menu.'));
    }

    public function destroy(Request $request, Product $product)
    {
        $this->own($request, $product);
        $product->delete();

        return redirect()->route('shop.products')->with('success', 'Product removed.');
    }

    /** Open or close the whole shop. */
    public function toggleOpen(Request $request)
    {
        $vendor = $request->user()->vendor;
        $vendor->update(['is_open' => !$vendor->is_open]);

        return back()->with('success', $vendor->is_open ? 'Your shop is open.' : 'Your shop is closed. Customers can’t see your products.');
    }

    private function own(Request $request, Product $product): void
    {
        abort_unless($product->vendor_id === $request->user()->vendor_id, 403);
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(Product::CATEGORIES))],
            'price' => ['required', 'numeric', 'min:1', 'max:9999'],
            'prep_time' => ['nullable', 'string', 'max:20'],
            'emoji' => ['nullable', 'string', 'max:10'],
        ]);

        $data['prep_time'] = ($data['prep_time'] ?? null) ?: '15-20 min';
        $data['emoji'] = ($data['emoji'] ?? null) ?: '🍽️';

        return $data;
    }
}
