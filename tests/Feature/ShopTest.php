<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ShopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function shopper(): User
    {
        $this->seed(ShopSeeder::class);

        return User::factory()->create();
    }

    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/menu', '/cart', '/checkout', '/orders'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_menu_lists_products_and_filters_by_category(): void
    {
        $user = $this->shopper();

        $this->actingAs($user)->get('/menu')->assertOk()->assertSee('Campus Lunch Combo')->assertSee('Iced Coffee');

        $this->actingAs($user)->get('/menu?category=drinks')
            ->assertOk()->assertSee('Iced Coffee')->assertDontSee('Campus Lunch Combo');

        $this->actingAs($user)->get('/menu?q=pandesal')
            ->assertOk()->assertSee('Pandesal (10 pcs)')->assertDontSee('Iced Coffee');
    }

    public function test_adding_the_same_product_twice_increases_quantity(): void
    {
        $user = $this->shopper();
        $product = Product::first();

        $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertOk()->assertJson(['ok' => true, 'count' => 1]);
        $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertOk()->assertJson(['count' => 2]);

        $this->assertSame(1, $user->cartItems()->count());
        $this->assertSame(2, $user->cartItems()->first()->quantity);
    }

    public function test_unavailable_products_cannot_be_added(): void
    {
        $user = $this->shopper();
        $product = Product::first();
        $product->update(['is_available' => false]);

        $this->actingAs($user)->postJson('/cart', ['product_id' => $product->id])->assertStatus(422);
        $this->assertSame(0, $user->cartItems()->count());
    }

    public function test_quantity_can_be_changed_and_items_removed(): void
    {
        $user = $this->shopper();
        $item = CartItem::create(['user_id' => $user->id, 'product_id' => Product::first()->id, 'quantity' => 1]);

        $this->actingAs($user)->patchJson("/cart/{$item->id}", ['quantity' => 4])
            ->assertOk()->assertJson(['quantity' => 4, 'count' => 4]);

        $this->actingAs($user)->patchJson("/cart/{$item->id}", ['quantity' => 99])->assertStatus(422);

        $this->actingAs($user)->deleteJson("/cart/{$item->id}")->assertOk()->assertJson(['count' => 0]);
        $this->assertSame(0, $user->cartItems()->count());
    }

    public function test_users_cannot_touch_someone_elses_cart(): void
    {
        $user = $this->shopper();
        $other = User::factory()->create();
        $item = CartItem::create(['user_id' => $other->id, 'product_id' => Product::first()->id, 'quantity' => 1]);

        $this->actingAs($user)->patchJson("/cart/{$item->id}", ['quantity' => 2])->assertForbidden();
        $this->actingAs($user)->deleteJson("/cart/{$item->id}")->assertForbidden();
    }

    public function test_checkout_needs_at_least_one_ticked_item(): void
    {
        $user = $this->shopper();

        $this->actingAs($user)->post('/cart/checkout', [])->assertSessionHasErrors('items');
        $this->actingAs($user)->get('/checkout')->assertRedirect('/cart');
    }

    public function test_only_ticked_items_are_ordered_and_the_rest_stay_in_the_cart(): void
    {
        $user = $this->shopper();
        [$a, $b, $c] = Product::orderBy('id')->take(3)->get();

        $ia = CartItem::create(['user_id' => $user->id, 'product_id' => $a->id, 'quantity' => 2]);
        $ib = CartItem::create(['user_id' => $user->id, 'product_id' => $b->id, 'quantity' => 1]);
        $ic = CartItem::create(['user_id' => $user->id, 'product_id' => $c->id, 'quantity' => 3]);

        // tick items A and C only
        $this->actingAs($user)->post('/cart/checkout', ['items' => [$ia->id, $ic->id]])->assertRedirect('/checkout');
        $this->actingAs($user)->get('/checkout')->assertOk()->assertSee($a->name)->assertSee($c->name)->assertDontSee($b->name);

        $this->actingAs($user)->post('/orders', [
            'items' => [$ia->id, $ic->id],
            'location_id' => 1,
            'notes' => 'No onions',
            'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $expectedSubtotal = $a->price * 2 + $c->price * 3;

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('pending', $order->status);
        $this->assertEquals($expectedSubtotal, $order->subtotal);
        $this->assertEquals($expectedSubtotal + \App\Support\Pricing::BASE_FEE + \App\Support\Pricing::SERVICE_FEE, $order->total);
        $this->assertCount(2, $order->items);
        $this->assertSame(['cod'], [$order->payment_method]);

        // B was not ticked, so it is still waiting in the cart; A and C are gone
        $this->assertSame([$ib->id], $user->cartItems()->pluck('id')->all());
    }

    public function test_order_requires_location_and_valid_payment_method(): void
    {
        $user = $this->shopper();
        $item = CartItem::create(['user_id' => $user->id, 'product_id' => Product::first()->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/orders', ['items' => [$item->id], 'payment_method' => 'cod'])
            ->assertSessionHasErrors('location_id');

        $this->actingAs($user)->post('/orders', ['items' => [$item->id], 'location_id' => 1, 'payment_method' => 'bitcoin'])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $user->cartItems()->count());
    }

    public function test_prices_come_from_the_database_not_the_browser(): void
    {
        $user = $this->shopper();
        $product = Product::first();
        $item = CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/orders', [
            'items' => [$item->id], 'location_id' => 1, 'payment_method' => 'cod',
            'total' => 1, 'subtotal' => 1, 'price' => 1,
        ]);

        $this->assertEquals($product->price + \App\Support\Pricing::BASE_FEE + \App\Support\Pricing::SERVICE_FEE, Order::first()->total);
    }

    public function test_cannot_order_someone_elses_cart_items(): void
    {
        $user = $this->shopper();
        $other = User::factory()->create();
        $item = CartItem::create(['user_id' => $other->id, 'product_id' => Product::first()->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/orders', [
            'items' => [$item->id], 'location_id' => 1, 'payment_method' => 'cod',
        ])->assertRedirect('/cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $other->cartItems()->count());
    }

    public function test_orders_are_private_and_pending_orders_can_be_cancelled(): void
    {
        $user = $this->shopper();
        $other = User::factory()->create();
        $item = CartItem::create(['user_id' => $user->id, 'product_id' => Product::first()->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/orders', ['items' => [$item->id], 'location_id' => 1, 'payment_method' => 'cod']);
        $order = Order::firstOrFail();

        $this->actingAs($user)->get('/orders')->assertOk()->assertSee($order->order_number);
        $this->actingAs($user)->get("/orders/{$order->id}")->assertOk()->assertSee($order->order_number);
        $this->actingAs($other)->get("/orders/{$order->id}")->assertForbidden();
        $this->actingAs($other)->patch("/orders/{$order->id}/cancel")->assertForbidden();

        $this->actingAs($user)->patch("/orders/{$order->id}/cancel")->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);

        // a cancelled order cannot be cancelled again
        $this->actingAs($user)->patch("/orders/{$order->id}/cancel")->assertSessionHas('error');
    }
}
