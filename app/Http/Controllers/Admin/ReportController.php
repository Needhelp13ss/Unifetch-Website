<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\TrackActivity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Admin reports: charts and graphs of how UniFetch is doing. */
class ReportController extends Controller
{
    public function index()
    {
        $since = now()->subDays(TrackActivity::ACTIVE_DAYS);
        $people = User::whereIn('role', ['customer', 'runner', 'shop']);

        $active = (clone $people)->where('last_active_at', '>=', $since)->count();
        $total = (clone $people)->count();

        $byRole = [];
        foreach (['customer' => 'Customers', 'runner' => 'Runners', 'shop' => 'Shops'] as $role => $label) {
            $all = User::where('role', $role)->count();
            $act = User::where('role', $role)->where('last_active_at', '>=', $since)->count();
            $byRole[] = ['label' => $label, 'active' => $act, 'inactive' => $all - $act];
        }

        // last 14 days
        $days = collect(range(13, 0))->map(fn ($d) => today()->subDays($d));
        $orders = Order::where('created_at', '>=', $days->first())->get(['created_at', 'status', 'service_fee']);
        $perDay = $days->map(fn (Carbon $d) => $orders->filter(fn ($o) => $o->created_at->isSameDay($d) && $o->status !== 'cancelled')->count());

        $delivered = Order::where('status', 'delivered')->where('delivered_at', '>=', $days->first())->get(['delivered_at', 'service_fee']);
        $profitPerDay = $days->map(fn (Carbon $d) => (float) $delivered->filter(fn ($o) => $o->delivered_at->isSameDay($d))->sum('service_fee'));

        $statuses = [];
        $colors = ['pending' => '#F2B134', 'preparing' => '#3E7BFA', 'on_the_way' => '#1A1A2E', 'delivered' => '#1FA187', 'cancelled' => '#9AA3B5'];
        foreach (Order::STATUSES as $key => $label) {
            $statuses[] = [$label, Order::where('status', $key)->count(), $colors[$key]];
        }

        // top shops by sales (delivered orders only)
        $topShops = OrderItem::select('vendor_name', DB::raw('SUM(price * quantity) as sales'))
            ->whereHas('order', fn ($o) => $o->where('status', 'delivered'))
            ->groupBy('vendor_name')->orderByDesc('sales')->limit(6)->get()
            ->map(fn ($r) => [$r->vendor_name, (float) $r->sales])->all();

        // new people per week by role (8 weeks)
        $weeks = collect(range(7, 0))->map(fn ($w) => now()->startOfWeek()->subWeeks($w));
        $joined = User::whereIn('role', ['customer', 'runner', 'shop'])->where('created_at', '>=', $weeks->first())->get(['created_at', 'role']);
        $weekCount = fn ($role) => $weeks->map(fn (Carbon $w) => $joined->filter(
            fn ($u) => $u->role === $role && $u->created_at->betweenIncluded($w, $w->copy()->endOfWeek())
        )->count())->all();

        $topRunners = Order::select('runner_id', DB::raw('COUNT(*) as jobs'))
            ->where('status', 'delivered')->whereNotNull('runner_id')
            ->groupBy('runner_id')->orderByDesc('jobs')->limit(6)->get();
        $names = User::whereIn('id', $topRunners->pluck('runner_id'))->pluck('name', 'id');

        return view('admin.reports', [
            'active' => $active,
            'total' => $total,
            'byRole' => $byRole,
            'dayLabels' => $days->map(fn ($d) => $d->format('M j'))->all(),
            'perDay' => $perDay->all(),
            'profitPerDay' => $profitPerDay->all(),
            'profitTotal' => (float) Order::where('status', 'delivered')->sum('service_fee'),
            'statuses' => $statuses,
            'topShops' => $topShops,
            'weekLabels' => $weeks->map(fn ($d) => $d->format('M j'))->all(),
            'newCustomers' => $weekCount('customer'),
            'newRunners' => $weekCount('runner'),
            'newShops' => $weekCount('shop'),
            'topRunners' => $topRunners->map(fn ($r) => [$names[$r->runner_id] ?? 'Removed runner', (int) $r->jobs])->all(),
        ]);
    }
}
