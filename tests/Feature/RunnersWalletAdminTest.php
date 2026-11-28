<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\Wallet;
use Database\Seeders\AdminSeeder;
use Database\Seeders\ShopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RunnersWalletAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShopSeeder::class);
        Storage::fake();
    }

    /** A pending food order (one product, qty 1) for $customer. */
    private function order(User $customer, string $payment = 'cod'): Order
    {
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => Product::first()->id, 'quantity' => 1]);
        $this->actingAs($customer)->post('/orders', [
            'items' => [$item->id], 'location_id' => 1, 'payment_method' => $payment,
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    // ---------------- roles & visibility ----------------

    public function test_customers_do_not_see_errand_services_but_runners_do(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create();

        // customers can use Errand Services, but the runner job board is hidden from them
        $this->actingAs($customer)->get('/dashboard')->assertOk()->assertSee('Errand Services')->assertDontSee('Runner Jobs');
        $this->actingAs($customer)->get('/menu')->assertOk()->assertDontSee('Runner Jobs');
        $this->actingAs($customer)->get('/jobs')->assertRedirect('/dashboard');

        $this->actingAs($runner)->get('/dashboard')->assertOk()->assertSee('Runner Jobs');
        $this->actingAs($runner)->get('/jobs')->assertOk()->assertSee('Waiting for a runner');
    }

    public function test_pending_runner_applicants_cannot_use_errand_services_yet(): void
    {
        $applicant = User::factory()->pendingRunner()->create();

        $this->actingAs($applicant)->get('/jobs')->assertRedirect('/dashboard');
        $this->flushSession(); // drop the "only for runners" notice before checking the page
        $this->actingAs($applicant)->get('/dashboard')->assertSee('Runner application pending')->assertDontSee('Runner Jobs');
    }

    private function runnerFields(array $over = []): array
    {
        return $over + [
            'portal_id' => 'UCLM-2201234', 'course' => 'BS Information Technology', 'year_level' => '3rd Year',
            'enrolled_confirmed' => '1', 'id_photo' => UploadedFile::fake()->image('id.jpg', 600, 400),
        ];
    }

    public function test_registering_as_runner_needs_verification_details_and_creates_a_pending_application(): void
    {
        Storage::fake('local');

        $this->post('/register', [
            'first_name' => 'Rey', 'last_name' => 'Runner', 'student_id' => '22-0001',
            'email' => 'rey@gmail.com', 'password' => 'password123', 'account_type' => 'runner',
        ] + $this->runnerFields())->assertRedirect('/login');

        $u = User::where('email', 'rey@gmail.com')->first();
        $this->assertSame('customer', $u->role);
        $this->assertSame('pending', $u->runner_status);
        $this->assertFalse($u->isRunner());
        $this->assertSame('UCLM-2201234', $u->portal_id);
        $this->assertSame('BS Information Technology', $u->course);
        $this->assertSame('3rd Year', $u->year_level);
        $this->assertTrue($u->enrolled_confirmed);
        Storage::disk('local')->assertExists($u->id_photo_path);
    }

    public function test_runner_registration_is_refused_without_the_verification_details(): void
    {
        Storage::fake('local');
        $base = ['first_name' => 'Rey', 'last_name' => 'Runner', 'student_id' => '22-0001', 'email' => 'rey@example.com', 'password' => 'password123', 'account_type' => 'runner'];

        $this->post('/register', $base)->assertSessionHasErrors(['portal_id', 'course', 'year_level', 'enrolled_confirmed', 'id_photo']);
        $this->post('/register', $base + $this->runnerFields(['enrolled_confirmed' => null]))->assertSessionHasErrors('enrolled_confirmed');
        $this->post('/register', $base + $this->runnerFields(['id_photo' => UploadedFile::fake()->create('id.pdf', 100, 'application/pdf')]))->assertSessionHasErrors('id_photo');
        $this->assertNull(User::where('email', 'rey@example.com')->first());
    }

    public function test_customers_can_register_with_any_email_and_no_runner_details(): void
    {
        $this->post('/register', [
            'first_name' => 'Cara', 'last_name' => 'Customer', 'student_id' => '22-0009',
            'email' => 'cara@gmail.com', 'password' => 'password123', 'account_type' => 'customer',
        ])->assertRedirect('/login')->assertSessionHasNoErrors();

        $u = User::where('email', 'cara@gmail.com')->firstOrFail();
        $this->assertNull($u->runner_status);
        $this->assertNull($u->id_photo_path);
    }

    public function test_existing_customer_can_apply_to_become_a_runner_with_their_id(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->get('/runner/apply')->assertOk()->assertSee('Portal ID')->assertSee('currently enrolled UCLM student');

        // missing pieces are refused
        $this->actingAs($user)->post('/runner/apply', [])->assertSessionHasErrors(['portal_id', 'course', 'year_level', 'enrolled_confirmed', 'id_photo']);
        $this->assertFalse($user->fresh()->hasPendingRunnerApplication());

        $this->actingAs($user)->post('/runner/apply', $this->runnerFields())->assertRedirect('/dashboard')->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->hasPendingRunnerApplication());
        $this->assertSame('UCLM-2201234', $user->portal_id);
        Storage::disk('local')->assertExists($user->id_photo_path);

        // already applied: the form is closed
        $this->actingAs($user)->get('/runner/apply')->assertRedirect('/dashboard');
    }

    public function test_a_portal_id_can_only_be_used_once(): void
    {
        Storage::fake('local');
        User::factory()->create(['portal_id' => 'UCLM-2201234']);
        $other = User::factory()->create();

        $this->actingAs($other)->post('/runner/apply', $this->runnerFields())->assertSessionHasErrors('portal_id');
    }

    public function test_only_the_admin_can_see_the_school_id_photo_and_it_shows_when_approving(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $applicant = User::factory()->create(['name' => 'Anna Applicant']);
        $this->actingAs($applicant)->post('/runner/apply', $this->runnerFields());
        $applicant->refresh();

        $this->actingAs($admin)->get('/admin/runners')->assertOk()
            ->assertSee('UCLM-2201234')->assertSee('BS Information Technology')->assertSee('3rd Year')
            ->assertSee('currently enrolled UCLM student')->assertSee('View school ID photo');
        $this->actingAs($admin)->get("/admin/runners/{$applicant->id}/id-photo")->assertOk();

        // nobody else, not even the owner, can open the private photo address
        $this->actingAs($applicant)->get("/admin/runners/{$applicant->id}/id-photo")->assertForbidden();
        $this->actingAs(User::factory()->runner()->create())->get("/admin/runners/{$applicant->id}/id-photo")->assertForbidden();

        // a user with no photo gives 404
        $this->actingAs($admin)->get('/admin/runners/' . User::factory()->create()->id . '/id-photo')->assertNotFound();
    }

    public function test_uclm_student_badge_shows_only_for_verified_runners(): void
    {
        Storage::fake('local');
        $applicant = User::factory()->create();
        $this->actingAs($applicant)->post('/runner/apply', $this->runnerFields());
        $applicant->refresh();

        // pending: no badge yet
        $this->actingAs($applicant)->get('/profile')->assertOk()->assertDontSee('UCLM STUDENT')->assertSee('Application pending');

        // approved by the admin: badge appears
        $this->actingAs(User::factory()->admin()->create())->patch("/admin/runners/{$applicant->id}/approve");
        $this->actingAs($applicant->fresh())->get('/profile')->assertSee('UCLM STUDENT');

        // a plain customer never gets it
        $this->actingAs(User::factory()->create())->get('/profile')->assertDontSee('UCLM STUDENT');
    }

    public function test_deleting_a_user_deletes_their_id_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/runner/apply', $this->runnerFields());
        $path = $user->fresh()->id_photo_path;
        Storage::disk('local')->assertExists($path);

        $this->actingAs(User::factory()->admin()->create())->delete("/admin/users/{$user->id}");
        Storage::disk('local')->assertMissing($path);
    }

    public function test_minimum_runner_fee_is_five_pesos_and_wording_says_ordering_not_food(): void
    {
        $user = User::factory()->create();
        $payload = $this->errandPayload(['fee' => 7]);

        $this->actingAs($user)->post('/errand-services', $this->errandPayload(['fee' => 4]))->assertSessionHasErrors('fee');
        $this->actingAs($user)->post('/errand-services', $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals(10, Order::firstOrFail()->total);
        $this->actingAs($user)->post('/custom-order', [
            'errand_title' => 'Water', 'errand_details' => 'Any', 'location_id' => 1, 'fee' => 7, 'payment_method' => 'cod',
        ])->assertSessionHasNoErrors();

        $this->flushSession();
        $this->actingAs($user)->get('/dashboard')->assertDontSee('Food Ordering')->assertDontSee('Order food');
        $this->actingAs($user)->get('/menu')->assertOk()->assertDontSee('Food Ordering');
    }

    public function test_cannot_make_yourself_admin_when_registering(): void
    {
        $this->post('/register', [
            'first_name' => 'Sly', 'last_name' => 'Fox', 'student_id' => '22-0002',
            'email' => 'sly@example.com', 'password' => 'password123', 'role' => 'admin', 'account_type' => 'admin',
        ])->assertSessionHasErrors('account_type');

        $this->assertNull(User::where('email', 'sly@example.com')->first());
    }

    // ---------------- looking for runners / runner flow ----------------

    public function test_new_orders_show_looking_for_runners_until_a_runner_takes_them(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create(['name' => 'Ronnie Runner']);
        Wallet::credit($runner, 500, 'topup', 'test'); // cash jobs need wallet cover
        $order = $this->order($customer);

        $this->assertSame('pending', $order->status);
        $this->actingAs($customer)->get("/orders/{$order->id}")->assertOk()
            ->assertSee('Looking for runners')->assertSee('Looking for a runner');

        $this->actingAs($runner)->post("/jobs/{$order->id}/accept")->assertRedirect();
        $order->refresh();
        $this->assertSame('preparing', $order->status);
        $this->assertSame($runner->id, $order->runner_id);

        // customer now sees who is running it and can no longer cancel
        $this->actingAs($customer)->get("/orders/{$order->id}")->assertSee('Ronnie Runner is on it');
        $this->actingAs($customer)->patch("/orders/{$order->id}/cancel")->assertSessionHas('error');
    }

    public function test_two_runners_cannot_take_the_same_job(): void
    {
        $customer = User::factory()->create();
        $a = User::factory()->runner()->create();
        $b = User::factory()->runner()->create();
        $order = $this->order($customer, 'cod');
        // wallet-free (cod) job: runners need wallet cover first
        Wallet::credit($a, 500, 'topup', 'test');
        Wallet::credit($b, 500, 'topup', 'test');

        $this->actingAs($a)->post("/jobs/{$order->id}/accept")->assertSessionHas('success');
        $this->actingAs($b)->post("/jobs/{$order->id}/accept")->assertSessionHas('error');
        $this->assertSame($a->id, $order->fresh()->runner_id);
    }

    public function test_runner_cannot_take_their_own_order(): void
    {
        $runner = User::factory()->runner()->create();
        Wallet::credit($runner, 500, 'topup', 'test');
        $order = $this->order($runner);

        $this->actingAs($runner)->post("/jobs/{$order->id}/accept")->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cash_job_needs_wallet_cover_and_remits_cash_on_delivery(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create();
        $order = $this->order($customer, 'cod');
        $owed = $order->cashToRemit();
        $this->assertGreaterThan(0, $owed);
        $this->assertEquals($order->total - \App\Support\Pricing::BASE_FEE, $owed);

        // empty wallet: refused
        $this->actingAs($runner)->post("/jobs/{$order->id}/accept")->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);

        // top up, then it works
        $this->actingAs($runner)->post('/wallet/topup', ['amount' => 1000])->assertSessionHas('success');
        $this->actingAs($runner)->post("/jobs/{$order->id}/accept")->assertSessionHas('success');
        $this->actingAs($runner)->post("/jobs/{$order->id}/start", ['photo' => UploadedFile::fake()->image('p.jpg')])->assertSessionHas('success');
        $this->actingAs($runner)->post("/jobs/{$order->id}/complete", ['photo' => UploadedFile::fake()->image('p.jpg')])->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertEquals($owed, $order->remitted);
        $this->assertEquals(1000 - $owed, $runner->fresh()->wallet_balance);
        $this->assertSame(1, WalletTransaction::where('type', 'remittance')->where('user_id', $runner->id)->count());
    }

    public function test_wallet_order_pays_up_front_and_runner_earns_the_fee(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create();
        Wallet::credit($customer, 1000, 'topup', 'test');
        $order = $this->order($customer, 'wallet');

        $this->assertEquals(1000 - $order->total, $customer->fresh()->wallet_balance);

        $this->actingAs($runner)->post("/jobs/{$order->id}/accept")->assertSessionHas('success');
        $this->actingAs($runner)->post("/jobs/{$order->id}/start", ['photo' => UploadedFile::fake()->image('p.jpg')]);
        $this->actingAs($runner)->post("/jobs/{$order->id}/complete", ['photo' => UploadedFile::fake()->image('p.jpg')]);

        $this->assertEquals(\App\Support\Pricing::BASE_FEE, $runner->fresh()->wallet_balance);
        $this->assertSame(0, WalletTransaction::where('type', 'remittance')->count());
    }

    public function test_runner_can_give_a_job_back(): void
    {
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create();
        Wallet::credit($runner, 500, 'topup', 'test');
        $order = $this->order($customer);

        $this->actingAs($runner)->post("/jobs/{$order->id}/accept");
        $this->actingAs($runner)->post("/jobs/{$order->id}/release")->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertNull($order->runner_id);
    }

    public function test_only_the_assigned_runner_can_change_a_job(): void
    {
        $customer = User::factory()->create();
        $a = User::factory()->runner()->create();
        $b = User::factory()->runner()->create();
        Wallet::credit($a, 500, 'topup', 'test');
        $order = $this->order($customer);
        $this->actingAs($a)->post("/jobs/{$order->id}/accept");

        $this->actingAs($b)->post("/jobs/{$order->id}/start", ['photo' => UploadedFile::fake()->image('p.jpg')])->assertForbidden();
        $this->actingAs($customer)->post("/jobs/{$order->id}/start", ['photo' => UploadedFile::fake()->image('p.jpg')])->assertRedirect('/dashboard');
    }

    // ---------------- wallet ----------------

    public function test_topup_validates_and_records_history(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/wallet/topup', ['amount' => 5])->assertSessionHasErrors('amount');
        $this->actingAs($user)->post('/wallet/topup', ['amount' => 999999])->assertSessionHasErrors('amount');
        $this->actingAs($user)->post('/wallet/topup', ['amount' => 250])->assertSessionHas('success');

        $this->assertEquals(250, $user->fresh()->wallet_balance);
        $this->actingAs($user)->get('/wallet')->assertOk()->assertSee('Wallet top-up')->assertSee('₱250');
    }

    public function test_cannot_pay_with_wallet_without_enough_money(): void
    {
        $user = User::factory()->create();
        $item = CartItem::create(['user_id' => $user->id, 'product_id' => Product::first()->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/orders', [
            'items' => [$item->id], 'location_id' => 1, 'payment_method' => 'wallet',
        ])->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $user->cartItems()->count());
        $this->assertEquals(0, $user->fresh()->wallet_balance);
    }

    public function test_cancelling_a_wallet_order_refunds_the_customer(): void
    {
        $user = User::factory()->create();
        Wallet::credit($user, 500, 'topup', 'test');
        $order = $this->order($user, 'wallet');
        $this->assertLessThan(500, $user->fresh()->wallet_balance);

        $this->actingAs($user)->patch("/orders/{$order->id}/cancel")->assertSessionHas('success');

        $this->assertEquals(500, $user->fresh()->wallet_balance);
        $this->assertSame(1, WalletTransaction::where('type', 'refund')->count());
    }

    // ---------------- custom errand ----------------

    public function test_customer_can_request_a_custom_errand(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/custom-order')->assertOk()->assertSee('Make a custom order');

        $this->actingAs($user)->post('/custom-order', [
            'errand_title' => 'Print my notes', 'errand_details' => '12 pages, B&W',
            'location_id' => 1, 'fee' => 50, 'payment_method' => 'cod', 'budget' => 60,
        ])->assertRedirect();

        $o = Order::firstOrFail();
        $this->assertSame('custom', $o->type);
        $this->assertSame('pending', $o->status);
        $this->assertEquals(53, $o->total);
        $this->assertEquals(3, $o->cashToRemit());
        $this->actingAs($user)->get("/orders/{$o->id}")->assertSee('Print my notes')->assertSee('Looking for runners');

        // visible to runners on the board
        $runner = User::factory()->runner()->create();
        $this->actingAs($runner)->get('/jobs')->assertSee('Print my notes');
    }

    public function test_custom_errand_fee_limits_and_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/custom-order', [
            'errand_title' => 'x', 'errand_details' => 'y', 'location_id' => 1, 'fee' => 4, 'payment_method' => 'cod',
        ])->assertSessionHasErrors('fee');

        $this->actingAs($user)->post('/custom-order', ['fee' => 50, 'payment_method' => 'cod'])
            ->assertSessionHasErrors(['errand_title', 'errand_details', 'location_id']);

        $this->assertSame(0, Order::count());
    }

    public function test_custom_errand_paid_by_wallet_and_refunded_on_cancel(): void
    {
        $user = User::factory()->create();
        Wallet::credit($user, 100, 'topup', 'test');

        $this->actingAs($user)->post('/custom-order', [
            'errand_title' => 'Buy charger', 'errand_details' => 'USB-C', 'location_id' => 1,
            'fee' => 60, 'payment_method' => 'wallet',
        ])->assertRedirect();

        $o = Order::firstOrFail();
        $this->assertEquals(37, $user->fresh()->wallet_balance);

        $this->actingAs($user)->patch("/orders/{$o->id}/cancel");
        $this->assertEquals(100, $user->fresh()->wallet_balance);
    }

    // ---------------- admin ----------------

    public function test_admin_seeder_creates_an_admin_that_lands_on_the_panel(): void
    {
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class); // safe to repeat
        $this->assertSame(1, User::where('role', 'admin')->count());

        $this->post('/login', ['email' => AdminSeeder::EMAIL, 'password' => AdminSeeder::PASSWORD])
            ->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Overview');
    }

    public function test_admin_cannot_order_or_use_shop_pages(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['/menu', '/cart', '/checkout', '/orders', '/wallet', '/custom-order', '/errand-services'] as $url) {
            $this->actingAs($admin)->get($url)->assertRedirect('/admin');
        }
        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin');
        $this->actingAs($admin)->postJson('/cart', ['product_id' => Product::first()->id])->assertForbidden();
        $this->actingAs($admin)->post('/orders', ['items' => [1], 'location_id' => 1, 'payment_method' => 'cod'])->assertRedirect('/admin');
        $this->assertSame(0, Order::count());
    }

    public function test_customers_and_runners_cannot_open_the_admin_panel(): void
    {
        foreach ([User::factory()->create(), User::factory()->runner()->create()] as $u) {
            foreach (['/admin', '/admin/runners', '/admin/users', '/admin/orders', '/admin/menu'] as $url) {
                $this->actingAs($u)->get($url)->assertForbidden();
            }
        }
    }

    public function test_guests_are_sent_to_login_for_the_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_approves_and_removes_runners(): void
    {
        $admin = User::factory()->admin()->create();
        $applicant = User::factory()->pendingRunner()->create(['name' => 'Anna Applicant']);

        $this->actingAs($admin)->get('/admin/runners')->assertOk()->assertSee('Anna Applicant');
        $this->actingAs($admin)->patch("/admin/runners/{$applicant->id}/approve")->assertSessionHas('success');

        $applicant->refresh();
        $this->assertTrue($applicant->isRunner());
        $this->actingAs($applicant)->get('/jobs')->assertOk();

        $this->actingAs($admin)->patch("/admin/runners/{$applicant->id}/revoke")->assertSessionHas('success');
        $this->assertFalse($applicant->fresh()->isRunner());
        $this->actingAs($applicant->fresh())->get('/jobs')->assertRedirect('/dashboard');
    }

    public function test_admin_can_reject_an_application(): void
    {
        $admin = User::factory()->admin()->create();
        $applicant = User::factory()->pendingRunner()->create();

        $this->actingAs($admin)->patch("/admin/runners/{$applicant->id}/reject");
        $this->assertSame('rejected', $applicant->fresh()->runner_status);
        $this->assertFalse($applicant->fresh()->isRunner());
    }

    public function test_admin_manages_orders_assign_cancel_and_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $runner = User::factory()->runner()->create();
        Wallet::credit($customer, 500, 'topup', 'test');
        $order = $this->order($customer, 'wallet');

        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee($order->order_number);
        $this->actingAs($admin)->get("/admin/orders/{$order->id}")->assertOk()->assertSee('Assign a runner');

        $this->actingAs($admin)->post("/admin/orders/{$order->id}/assign", ['runner_id' => $runner->id])->assertSessionHas('success');
        $this->assertSame('preparing', $order->fresh()->status);

        // can't delete an active order, can after cancelling (and the customer is refunded)
        $this->actingAs($admin)->delete("/admin/orders/{$order->id}")->assertSessionHas('error');
        $this->actingAs($admin)->post("/admin/orders/{$order->id}/cancel")->assertSessionHas('success');
        $this->assertEquals(500, $customer->fresh()->wallet_balance);

        $this->actingAs($admin)->delete("/admin/orders/{$order->id}")->assertRedirect('/admin/orders');
        $this->assertSame(0, Order::count());
    }

    public function test_admin_can_adjust_wallets_and_delete_users_but_not_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->post("/admin/users/{$user->id}/wallet", ['direction' => 'add', 'amount' => 300, 'reason' => 'Cash received'])->assertSessionHas('success');
        $this->assertEquals(300, $user->fresh()->wallet_balance);
        $this->actingAs($admin)->post("/admin/users/{$user->id}/wallet", ['direction' => 'deduct', 'amount' => 100, 'reason' => 'Fix'])->assertSessionHas('success');
        $this->assertEquals(200, $user->fresh()->wallet_balance);

        $this->actingAs($admin)->get('/admin/users?q=' . urlencode($user->name))->assertOk()->assertSee($user->name);
        $this->actingAs($admin)->get("/admin/users/{$user->id}")->assertOk()->assertSee('Cash received');

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->actingAs($admin)->delete("/admin/users/{$user->id}")->assertRedirect('/admin/users');
        $this->assertNull(User::find($user->id));
    }

    public function test_admin_can_add_change_hide_and_remove_menu_items(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/vendors', ['name' => 'Test Kitchen', 'location' => 'Canteen'])->assertRedirect();
        $vendor = \App\Models\Vendor::where('name', 'Test Kitchen')->firstOrFail();

        $this->actingAs($admin)->post("/admin/vendors/{$vendor->id}/products", [
            'name' => 'Test Burger', 'category' => 'meals', 'price' => 99,
        ])->assertSessionHas('success');
        $product = $vendor->products()->firstOrFail();
        $this->assertSame('15-20 min', $product->prep_time);

        $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'name' => 'Test Burger XL', 'category' => 'meals', 'price' => 129, 'is_available' => 1,
        ])->assertRedirect();
        $this->assertEquals(129, $product->fresh()->price);

        $customer = User::factory()->create();
        $this->actingAs($customer)->get('/menu')->assertSee('Test Burger XL');

        $this->actingAs($admin)->patch("/admin/products/{$product->id}/toggle");
        $this->flushSession(); // the "now hidden" flash message would otherwise show the name
        $this->actingAs($customer)->get('/menu')->assertDontSee('Test Burger XL');

        $this->actingAs($admin)->delete("/admin/vendors/{$vendor->id}")->assertRedirect('/admin/menu');
        $this->assertNull(\App\Models\Product::find($product->id));
    }

    // ---------------- Errand Services (customer asks a runner to do something) ----------------

    private function errandPayload(array $over = []): array
    {
        return $over + [
            'errand_category' => 'Wait in line', 'errand_title' => 'Line up at the cashier',
            'errand_details' => 'Pay my balance, I will send the exact amount.',
            'pickup_location' => 'Cashier, Main Building', 'location_id' => 1,
            'fee' => 80, 'payment_method' => 'cod',
        ];
    }

    public function test_customer_can_post_an_errand_and_a_runner_sees_it(): void
    {
        $user = User::factory()->create();
        $runner = User::factory()->runner()->create(['wallet_balance' => 100]);

        $this->actingAs($user)->get('/errand-services')->assertOk()
            ->assertSee('Let a runner do it for you')->assertSee('Pick up a parcel')->assertSee('Wait in line');

        $this->actingAs($user)->post('/errand-services', $this->errandPayload(['budget' => 150]))->assertRedirect();

        $o = Order::firstOrFail();
        $this->assertSame('errand', $o->type);
        $this->assertSame('Wait in line', $o->errand_category);
        $this->assertSame('pending', $o->status);
        $this->assertEquals(83, $o->total);
        $this->assertEquals(3, $o->cashToRemit());

        $this->actingAs($user)->get("/orders/{$o->id}")->assertSee('Looking for runners')->assertSee('Line up at the cashier')->assertSee('Cashier, Main Building');
        $this->actingAs($user)->get('/orders')->assertSee('Errand');

        $this->actingAs($runner)->get('/jobs')->assertSee('Line up at the cashier')->assertSee('Wait in line')->assertSee('Go to');

        // a runner takes it and finishes it: the runner keeps the fee and hands over the 3 peso service fee
        $this->actingAs($runner)->post("/jobs/{$o->id}/accept")->assertSessionHas('success');
        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => UploadedFile::fake()->image('p.jpg')]);
        $this->actingAs($runner)->post("/jobs/{$o->id}/complete", ['photo' => UploadedFile::fake()->image('p.jpg')])->assertSessionHas('success');
        $this->assertSame('delivered', $o->fresh()->status);
        $this->assertEquals(3, WalletTransaction::where('type', 'remittance')->sum('amount') * -1 ?: WalletTransaction::where('type', 'remittance')->sum('amount'));
    }

    public function test_errand_needs_a_valid_category_locations_and_fee(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/errand-services', $this->errandPayload(['errand_category' => 'Hack the planet']))->assertSessionHasErrors('errand_category');
        $this->actingAs($user)->post('/errand-services', $this->errandPayload(['pickup_location' => '']))->assertSessionHasErrors('pickup_location');
        $this->actingAs($user)->post('/errand-services', $this->errandPayload(['fee' => 4]))->assertSessionHasErrors('fee');
        $this->actingAs($user)->post('/errand-services', [])->assertSessionHasErrors(['errand_category', 'errand_title', 'errand_details', 'pickup_location', 'location_id', 'fee']);
        $this->assertSame(0, Order::count());
    }

    public function test_errand_paid_by_wallet_pays_the_runner_the_fee(): void
    {
        $user = User::factory()->create();
        $runner = User::factory()->runner()->create();
        Wallet::credit($user, 200, 'topup', 'test');

        $this->actingAs($user)->post('/errand-services', $this->errandPayload(['errand_category' => 'Pick up a parcel', 'payment_method' => 'wallet']))->assertRedirect();
        $o = Order::firstOrFail();
        $this->assertEquals(117, $user->fresh()->wallet_balance);

        $this->actingAs($runner)->post("/jobs/{$o->id}/accept");
        $this->actingAs($runner)->post("/jobs/{$o->id}/start", ['photo' => UploadedFile::fake()->image('p.jpg')]);
        $this->actingAs($runner)->post("/jobs/{$o->id}/complete", ['photo' => UploadedFile::fake()->image('p.jpg')]);
        $this->assertEquals(80, $runner->fresh()->wallet_balance);
    }

    public function test_custom_order_and_errand_are_different_things(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/custom-order', [
            'errand_title' => 'Chicken sandwich', 'errand_details' => 'No mayo', 'location_id' => 1,
            'fee' => 40, 'payment_method' => 'cod',
        ])->assertRedirect();
        $this->actingAs($user)->post('/errand-services', $this->errandPayload())->assertRedirect();

        $this->assertSame(['custom', 'errand'], Order::orderBy('id')->pluck('type')->all());
        $this->assertNull(Order::where('type', 'custom')->first()->errand_category);

        // menu links to the custom order, not to errands
        $this->actingAs($user)->get('/menu')->assertSee('/custom-order');
    }
}
