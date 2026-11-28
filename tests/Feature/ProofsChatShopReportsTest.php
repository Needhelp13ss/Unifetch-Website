<?php

namespace Tests\Feature;

use App\Models\CampusLocation;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Support\Pricing;
use Database\Seeders\AdminSeeder;
use Database\Seeders\ShopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofsChatShopReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShopSeeder::class);
        Storage::fake();
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->image('p.jpg');
    }

    private function shopUser(?Vendor $vendor = null): User
    {
        $vendor ??= Vendor::firstOrFail();
        $u = User::factory()->create(['email' => 'shop' . $vendor->id . '@example.com']);
        $u->forceFill(['role' => 'shop', 'vendor_id' => $vendor->id])->save();

        return $u;
    }

    private function place(string $name): CampusLocation
    {
        return CampusLocation::where('name', $name)->firstOrFail();
    }

    private function order(User $customer, int $locationId = 1): Order
    {
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => Product::first()->id, 'quantity' => 1]);
        $this->actingAs($customer)->post('/orders', [
            'items' => [$item->id], 'location_id' => $locationId, 'payment_method' => 'cod',
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    // ---------------- pricing ----------------

    public function test_pricing_rules(): void
    {
        $this->assertSame(7, Pricing::runnerFee(0));
        $this->assertSame(7, Pricing::runnerFee(24));
        $this->assertSame(9, Pricing::runnerFee(25));
        $this->assertSame(9, Pricing::runnerFee(49));
        $this->assertSame(11, Pricing::runnerFee(50));
        $this->assertSame(3, Pricing::SERVICE_FEE);
    }

    public function test_distance_adds_two_pesos_per_25_meters_and_three_pesos_profit(): void
    {
        $customer = User::factory()->create();
        $vendor = Vendor::firstOrFail();
        $vendor->update(['campus_location_id' => $this->place('Main Gate')->id]);
        $far = $this->place('Annex Building');

        $meters = Pricing::meters($this->place('Main Gate'), $far);
        $this->assertGreaterThan(100, $meters);

        $items = Product::where('vendor_id', $vendor->id)->first();
        $cart = CartItem::create(['user_id' => $customer->id, 'product_id' => $items->id, 'quantity' => 1]);
        $this->actingAs($customer)->post('/orders', ['items' => [$cart->id], 'location_id' => $far->id, 'payment_method' => 'cod'])->assertRedirect();

        $o = Order::firstOrFail();
        $this->assertSame($meters, (int) $o->distance_m);
        $this->assertEquals(Pricing::runnerFee($meters), $o->delivery_fee);
        $this->assertEquals(3, $o->service_fee);
        $this->assertEquals($items->price + Pricing::runnerFee($meters) + 3, $o->total);
    }

    public function test_quote_endpoint(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->getJson('/quote?to=' . $this->place('Library')->id)
            ->assertOk()->assertJson(['runner_fee' => 7, 'service_fee' => 3, 'meters' => 0, 'ready' => true]);
    }

    public function test_custom_order_fee_cannot_be_below_the_distance_quote(): void
    {
        $u = User::factory()->create();
        $payload = [
            'errand_title' => 'x', 'errand_details' => 'y', 'payment_method' => 'cod',
            'pickup_id' => $this->place('Main Gate')->id, 'location_id' => $this->place('Annex Building')->id,
        ];
        $quote = Pricing::runnerFee(Pricing::meters($this->place('Main Gate'), $this->place('Annex Building')));

        $this->actingAs($u)->post('/custom-order', $payload + ['fee' => 7])->assertSessionHasErrors('fee');
        $this->actingAs($u)->post('/custom-order', $payload + ['fee' => $quote])->assertSessionHasNoErrors();
        $this->assertEquals($quote + 3, Order::firstOrFail()->total);
    }

    // ---------------- photo proofs ----------------

    public function test_runner_needs_photos_to_start_and_finish(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create(['wallet_balance' => 1000]);
        $o = $this->order($customer);
        $this->actingAs($runner)->post("/jobs/{$o->id}/accept");

        $this->actingAs($runner)->post("/jobs/{$o->id}/start")->assertSessionHas('error');
        $this->assertSame('preparing', $o->fresh()->status);
        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => $this->photo()])->assertSessionHas('success');
        $this->assertSame('on_the_way', $o->fresh()->status);

        $this->actingAs($runner)->post("/jobs/{$o->id}/complete")->assertSessionHas('error');
        $this->assertSame('on_the_way', $o->fresh()->status);
        $this->actingAs($runner)->post("/jobs/{$o->id}/complete", ['photo' => $this->photo()])->assertSessionHas('success');

        $o->refresh();
        $this->assertSame('delivered', $o->status);
        $this->assertSame(['runner_delivery', 'runner_pickup'], $o->photos()->orderBy('kind')->pluck('kind')->all());
    }

    public function test_non_images_are_rejected(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create(['wallet_balance' => 1000]);
        $o = $this->order($customer);
        $this->actingAs($runner)->post("/jobs/{$o->id}/accept");

        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('photo');
        $this->assertSame('preparing', $o->fresh()->status);
    }

    public function test_shop_and_customer_proofs_and_who_may_upload(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create(['wallet_balance' => 1000]);
        $o = $this->order($customer);
        $shopVendor = Vendor::where('name', $o->items->first()->vendor_name)->firstOrFail();
        $shop = $this->shopUser($shopVendor);
        $otherShop = $this->shopUser(Vendor::where('id', '!=', $shopVendor->id)->first());

        // no runner yet: the shop can't add the handover photo
        $this->actingAs($shop)->post("/orders/{$o->id}/photos", ['kind' => 'shop_handover', 'photo' => $this->photo()])->assertSessionHas('error');

        $this->actingAs($runner)->post("/jobs/{$o->id}/accept");

        // another shop has nothing to do with this order
        $this->actingAs($otherShop)->post("/orders/{$o->id}/photos", ['kind' => 'shop_handover', 'photo' => $this->photo()])->assertForbidden();
        // the customer can't upload the shop's photo
        $this->actingAs($customer)->post("/orders/{$o->id}/photos", ['kind' => 'shop_handover', 'photo' => $this->photo()])->assertSessionHas('error');

        $this->actingAs($shop)->post("/orders/{$o->id}/photos", ['kind' => 'shop_handover', 'photo' => $this->photo()])->assertSessionHas('success');

        // customer can only add "received" after delivery
        $this->actingAs($customer)->post("/orders/{$o->id}/photos", ['kind' => 'customer_received', 'photo' => $this->photo()])->assertSessionHas('error');

        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => $this->photo()]);
        $this->actingAs($runner)->post("/jobs/{$o->id}/complete", ['photo' => $this->photo()]);
        $this->actingAs($customer)->post("/orders/{$o->id}/photos", ['kind' => 'customer_received', 'photo' => $this->photo()])->assertSessionHas('success');

        $this->assertSame(4, OrderPhoto::where('order_id', $o->id)->count());

        // the photos are private: customer, runner, shop and admin see them, strangers don't
        $photo = OrderPhoto::where('kind', 'customer_received')->first();
        $this->actingAs($runner)->get("/orders/{$o->id}/photos/{$photo->id}")->assertOk();
        $this->actingAs($shop)->get("/orders/{$o->id}/photos/{$photo->id}")->assertOk();
        $this->actingAs(User::factory()->create())->get("/orders/{$o->id}/photos/{$photo->id}")->assertForbidden();
        $this->actingAs($otherShop)->get("/orders/{$o->id}/photos/{$photo->id}")->assertForbidden();

        $this->seed(AdminSeeder::class);
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get("/admin/orders/{$o->id}/photos/{$photo->id}")->assertOk();
    }

    public function test_proof_of_delivery_shows_day_time_and_both_names(): void
    {
        $customer = User::factory()->create(['name' => 'Cora Customer']);
        $runner = User::factory()->runner()->create(['wallet_balance' => 1000, 'name' => 'Ronnie Runner']);
        $o = $this->order($customer);
        $this->actingAs($runner)->post("/jobs/{$o->id}/accept");
        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => $this->photo()]);
        $this->actingAs($runner)->post("/jobs/{$o->id}/complete", ['photo' => $this->photo()]);

        $day = $o->fresh()->delivered_at->format('l, M j, Y');
        $this->actingAs($customer)->get("/orders/{$o->id}")->assertSee('Proof of delivery')->assertSee($day)
            ->assertSee('Cora Customer')->assertSee('Ronnie Runner');
        $this->actingAs($runner)->get("/orders/{$o->id}")->assertOk()->assertSee('Proof of delivery');
    }

    // ---------------- history ----------------

    public function test_history_lists_my_orders_and_runs_with_names(): void
    {
        $customer = User::factory()->create(['name' => 'Cora Customer']);
        $runner = User::factory()->runner()->create(['wallet_balance' => 1000, 'name' => 'Ronnie Runner']);
        $mine = $this->order($customer);
        $this->actingAs($runner)->post("/jobs/{$mine->id}/accept");

        $this->actingAs($customer)->get('/history')->assertOk()->assertSee($mine->order_number)->assertSee('Ronnie Runner')->assertSee('Cora Customer');
        $this->actingAs($runner)->get('/history')->assertOk()->assertSee($mine->order_number);
        // somebody else's history doesn't show it
        $this->actingAs(User::factory()->create())->get('/history')->assertOk()->assertDontSee($mine->order_number);
        // strangers can't open the order
        $this->actingAs(User::factory()->create())->get("/orders/{$mine->id}")->assertForbidden();
    }

    // ---------------- chat ----------------

    public function test_customer_and_runner_can_chat_only_while_the_job_is_running(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create(['wallet_balance' => 1000]);
        $stranger = User::factory()->create();
        $o = $this->order($customer);

        // nobody to talk to yet
        $this->actingAs($customer)->postJson("/orders/{$o->id}/messages", ['body' => 'hi'])->assertForbidden();

        $this->actingAs($runner)->post("/jobs/{$o->id}/accept");
        $this->actingAs($customer)->postJson("/orders/{$o->id}/messages", ['body' => 'Please bring ketchup'])->assertOk();
        $this->actingAs($runner)->postJson("/orders/{$o->id}/messages", ['body' => 'Sure!'])->assertOk();
        $this->actingAs($stranger)->postJson("/orders/{$o->id}/messages", ['body' => 'hey'])->assertForbidden();
        $this->actingAs($stranger)->getJson("/orders/{$o->id}/messages")->assertForbidden();
        $this->actingAs($customer)->postJson("/orders/{$o->id}/messages", ['body' => ''])->assertStatus(422);

        $feed = $this->actingAs($customer)->getJson("/orders/{$o->id}/messages")->assertOk()->json();
        $this->assertCount(2, $feed['messages']);
        $this->assertTrue($feed['messages'][0]['mine']);
        $this->assertFalse($feed['messages'][1]['mine']);
        $after = $this->actingAs($customer)->getJson("/orders/{$o->id}/messages?after=" . $feed['messages'][0]['id'])->json();
        $this->assertCount(1, $after['messages']);

        $this->actingAs($customer)->get("/orders/{$o->id}")->assertSee('Chat with');

        // finished: still readable, no longer writable
        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => $this->photo()]);
        $this->actingAs($runner)->post("/jobs/{$o->id}/complete", ['photo' => $this->photo()]);
        $this->actingAs($customer)->postJson("/orders/{$o->id}/messages", ['body' => 'thanks'])->assertStatus(422);
        $this->actingAs($customer)->getJson("/orders/{$o->id}/messages")->assertOk();
    }

    // ---------------- shop accounts ----------------

    public function test_admin_creates_a_shop_account_and_the_seller_manages_only_their_shop(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = User::where('role', 'admin')->first();
        $vendor = Vendor::firstOrFail();
        $other = Vendor::where('id', '!=', $vendor->id)->first();

        $this->actingAs($admin)->post("/admin/vendors/{$vendor->id}/account", [
            'name' => 'Aling Nena', 'email' => 'nena@example.com', 'password' => 'secret123',
        ])->assertSessionHasNoErrors();

        $seller = User::where('email', 'nena@example.com')->firstOrFail();
        $this->assertTrue($seller->isShop());
        $this->assertSame($vendor->id, $seller->vendor_id);

        // logs in and lands on the shop panel
        auth()->logout();
        $this->post('/login', ['email' => 'nena@example.com', 'password' => 'secret123'])->assertRedirect('/shop');
        $this->get('/shop')->assertOk()->assertSee($vendor->name);

        // add, edit, hide own product
        $this->post('/shop/products', ['name' => 'Siomai', 'category' => 'meals', 'price' => 25])->assertSessionHasNoErrors();
        $p = Product::where('name', 'Siomai')->firstOrFail();
        $this->assertSame($vendor->id, $p->vendor_id);
        $this->put("/shop/products/{$p->id}", ['name' => 'Siomai 4pc', 'category' => 'meals', 'price' => 30, 'is_available' => 1])->assertRedirect('/shop/products');
        $this->patch("/shop/products/{$p->id}/toggle");
        $this->assertFalse($p->fresh()->is_available);

        // another shop's product is off limits
        $foreign = Product::where('vendor_id', $other->id)->first();
        $this->put("/shop/products/{$foreign->id}", ['name' => 'Hacked', 'category' => 'meals', 'price' => 1])->assertForbidden();
        $this->delete("/shop/products/{$foreign->id}")->assertForbidden();

        // open / close
        $this->patch('/shop/open');
        $this->assertFalse($vendor->fresh()->is_open);

        // shops can't order, and customers can't open the panel
        $this->get('/menu')->assertRedirect('/shop');
        $this->get('/cart')->assertRedirect('/shop');
        $this->actingAs(User::factory()->create())->get('/shop')->assertForbidden();
    }

    public function test_shop_account_rules(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = User::where('role', 'admin')->first();
        $v = Vendor::firstOrFail();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)->post("/admin/vendors/{$v->id}/account", ['name' => 'A', 'email' => 'taken@example.com', 'password' => 'secret123'])
            ->assertSessionHasErrors('email');
        $this->actingAs($admin)->post("/admin/vendors/{$v->id}/account", ['name' => 'A', 'email' => 'new@example.com', 'password' => 'short'])
            ->assertSessionHasErrors('password');
        // only the admin may create one
        $this->actingAs(User::factory()->create())->post("/admin/vendors/{$v->id}/account", ['name' => 'A', 'email' => 'x@example.com', 'password' => 'secret123'])->assertForbidden();
        // people can't make themselves a shop
        $this->post('/logout');
        $this->post('/register', ['account_type' => 'shop'])->assertSessionHasErrors();
    }

    public function test_shop_sees_only_its_orders_and_history(): void
    {
        $customer = User::factory()->create();
        $o = $this->order($customer);
        $vendor = Vendor::where('name', $o->items->first()->vendor_name)->firstOrFail();
        $mine = $this->shopUser($vendor);
        $other = $this->shopUser(Vendor::where('id', '!=', $vendor->id)->first());

        $this->actingAs($mine)->get('/shop')->assertSee($o->order_number);
        $this->actingAs($other)->get('/shop')->assertDontSee($o->order_number);
        $this->actingAs($mine)->get('/history')->assertSee($o->order_number);
        $this->actingAs($other)->get('/history')->assertDontSee($o->order_number);
        $this->actingAs($mine)->get("/orders/{$o->id}")->assertOk();
        $this->actingAs($other)->get("/orders/{$o->id}")->assertForbidden();
    }

    // ---------------- admin reports ----------------

    public function test_activity_is_tracked_and_reports_render_charts(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = User::where('role', 'admin')->first();

        $active = User::factory()->create();
        $stale = User::factory()->create();
        $stale->forceFill(['last_active_at' => now()->subDays(60)])->save();
        $this->assertNull($active->last_active_at);

        $this->actingAs($active)->get('/dashboard');
        $this->assertNotNull($active->fresh()->last_active_at);

        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create(['wallet_balance' => 1000]);
        $o = $this->order($customer);
        $this->actingAs($runner)->post("/jobs/{$o->id}/accept");
        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => $this->photo()]);
        $this->actingAs($runner)->post("/jobs/{$o->id}/complete", ['photo' => $this->photo()]);

        $res = $this->actingAs($admin)->get('/admin/reports')->assertOk()
            ->assertSee('Active vs inactive users')->assertSee('Orders per day')->assertSee('profit per day')
            ->assertSee('Orders by status')->assertSee('Top shops')->assertSee('Top runners')->assertSee('New users per week');
        $res->assertSee('<svg', false);
        $this->assertGreaterThanOrEqual(5, substr_count($res->getContent(), '<svg'));

        // non-admins can't see reports
        $this->actingAs($customer)->get('/admin/reports')->assertForbidden();
    }

    public function test_admin_manages_campus_places_and_vendor_place(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->post('/admin/locations', ['name' => 'Dorm', 'latitude' => 10.33, 'longitude' => 123.95])->assertSessionHasNoErrors();
        $dorm = CampusLocation::where('name', 'Dorm')->firstOrFail();
        $this->actingAs($admin)->put("/admin/locations/{$dorm->id}", ['name' => 'Dorm', 'latitude' => 11, 'longitude' => 124])->assertSessionHasNoErrors();
        $this->assertEquals(11, $dorm->fresh()->latitude);
        $this->actingAs($admin)->post('/admin/locations', ['name' => 'Dorm', 'latitude' => 1, 'longitude' => 1])->assertSessionHasErrors('name');
        $this->actingAs($admin)->post('/admin/locations', ['name' => 'Bad', 'latitude' => 999, 'longitude' => 1])->assertSessionHasErrors('latitude');
        $this->actingAs($admin)->get('/admin/locations')->assertOk()->assertSee('Dorm');

        $v = Vendor::firstOrFail();
        $this->actingAs($admin)->put("/admin/vendors/{$v->id}", ['name' => $v->name, 'campus_location_id' => $dorm->id, 'is_open' => 1])->assertSessionHasNoErrors();
        $this->assertSame($dorm->id, $v->fresh()->campus_location_id);

        $this->actingAs($admin)->delete("/admin/locations/{$dorm->id}");
        $this->assertNull($v->fresh()->campus_location_id);
    }
}
