<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->cartItems()
            ->with('product.vendor')
            ->latest('updated_at')
            ->get();

        return view('cart', [
            'items' => $items,
            'deliveryFee' => Order::DELIVERY_FEE,
        ]);
    }

    /** Add a product to the cart (or +quantity if it is already there). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:' . CartItem::MAX_QUANTITY],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if (!$product->is_available) {
            return $this->respond($request, 'Sorry, that item is not available right now.', false, 422);
        }

        $item = $request->user()->cartItems()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = min(CartItem::MAX_QUANTITY, ($item->exists ? $item->quantity : 0) + ($data['quantity'] ?? 1));
        $item->save();

        return $this->respond($request, "{$product->name} added to your cart.");
    }

    /** Change the quantity of one cart item. */
    public function update(Request $request, CartItem $cartItem)
    {
        $this->ownedBy($request, $cartItem);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . CartItem::MAX_QUANTITY],
        ]);

        $cartItem->update(['quantity' => $data['quantity']]);
        $cartItem->load('product');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'quantity' => $cartItem->quantity,
                'line_total' => $cartItem->line_total,
                'line_total_text' => Money::format($cartItem->line_total),
                'count' => $this->cartCount($request),
            ]);
        }

        return back();
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $this->ownedBy($request, $cartItem);
        $cartItem->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'count' => $this->cartCount($request)]);
        }

        return back()->with('success', 'Item removed from your cart.');
    }

    /**
     * The user ticked the items they want to buy now: remember them and go to checkout.
     * Items that were not ticked simply stay in the cart.
     */
    public function select(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer'],
        ], [
            'items.required' => 'Tick at least one item to check out.',
            'items.min' => 'Tick at least one item to check out.',
        ]);

        $ids = $request->user()->cartItems()->whereIn('id', $data['items'])->pluck('id')->all();

        if (empty($ids)) {
            return redirect()->route('cart')->with('error', 'Tick at least one item to check out.');
        }

        $request->session()->put('checkout_items', $ids);

        return redirect()->route('checkout');
    }

    private function ownedBy(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->user_id === $request->user()->id, 403);
    }

    private function cartCount(Request $request): int
    {
        return (int) $request->user()->cartItems()->sum('quantity');
    }

    private function respond(Request $request, string $message, bool $ok = true, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => $ok,
                'message' => $message,
                'count' => $this->cartCount($request),
            ], $status);
        }

        return back()->with($ok ? 'success' : 'error', $message);
    }
}
