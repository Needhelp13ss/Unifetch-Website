<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin: add, change and remove vendors and products. */
class MenuController extends Controller
{
    public function index()
    {
        return view('admin.menu', ['vendors' => Vendor::withCount('products')->orderBy('name')->get()]);
    }

    public function storeVendor(Request $request)
    {
        $vendor = Vendor::create($this->vendorData($request) + ['is_open' => true]);

        return redirect()->route('admin.vendors.show', $vendor)->with('success', "{$vendor->name} added. Now add its products.");
    }

    public function showVendor(Vendor $vendor)
    {
        return view('admin.vendor', [
            'vendor' => $vendor,
            'products' => $vendor->products()->orderBy('category')->orderBy('name')->get(),
            'categories' => Product::CATEGORIES,
        ]);
    }

    public function updateVendor(Request $request, Vendor $vendor)
    {
        $vendor->update($this->vendorData($request) + ['is_open' => $request->boolean('is_open')]);

        return back()->with('success', 'Vendor saved.');
    }

    public function destroyVendor(Vendor $vendor)
    {
        $name = $vendor->name;
        $vendor->delete();

        return redirect()->route('admin.menu')->with('success', "{$name} and its products were removed.");
    }

    public function storeProduct(Request $request, Vendor $vendor)
    {
        $vendor->products()->create($this->productData($request) + ['is_available' => true, 'bg_color' => '#FDE7C8']);

        return back()->with('success', 'Product added.');
    }

    public function editProduct(Product $product)
    {
        return view('admin.product', ['product' => $product->load('vendor'), 'categories' => Product::CATEGORIES]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $product->update($this->productData($request) + ['is_available' => $request->boolean('is_available')]);

        return redirect()->route('admin.vendors.show', $product->vendor_id)->with('success', 'Product saved.');
    }

    public function toggleProduct(Product $product)
    {
        $product->update(['is_available' => !$product->is_available]);

        return back()->with('success', $product->name . ($product->is_available ? ' is available again.' : ' is now hidden from the menu.'));
    }

    public function destroyProduct(Product $product)
    {
        $vendorId = $product->vendor_id;
        $product->delete();

        return redirect()->route('admin.vendors.show', $vendorId)->with('success', 'Product removed.');
    }

    private function vendorData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:120'],
        ]);
    }

    private function productData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(Product::CATEGORIES))],
            'price' => ['required', 'numeric', 'min:1', 'max:9999'],
            'prep_time' => ['nullable', 'string', 'max:20'],
            'emoji' => ['nullable', 'string', 'max:10'],
        ]);

        // the columns are not nullable, so fall back to the same defaults as the table
        $data['prep_time'] = ($data['prep_time'] ?? null) ?: '15-20 min';
        $data['emoji'] = ($data['emoji'] ?? null) ?: '🍽️';

        return $data;
    }
}
