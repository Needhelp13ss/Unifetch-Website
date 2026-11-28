<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPhoto;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class RunnerController extends Controller
{
    /**
     * What every runner applicant must give us (used by the apply form and by registration).
     * $required is false when the applicant is not applying (e.g. a normal customer signing up).
     */
    public static function applicationRules(?int $ignoreUserId = null, ?string $onlyWhenField = null): array
    {
        return [
            'portal_id' => [$onlyWhenField ? 'required_if:' . $onlyWhenField . ',runner' : 'required', 'nullable', 'string', 'max:50',
                Rule::unique('users', 'portal_id')->ignore($ignoreUserId)],
            'course' => [$onlyWhenField ? 'required_if:' . $onlyWhenField . ',runner' : 'required', 'nullable', 'string', 'max:100'],
            'year_level' => [$onlyWhenField ? 'required_if:' . $onlyWhenField . ',runner' : 'required', 'nullable', Rule::in(ProfileController::YEAR_LEVELS)],
            'enrolled_confirmed' => [$onlyWhenField ? 'accepted_if:' . $onlyWhenField . ',runner' : 'accepted'],
            'id_photo' => [$onlyWhenField ? 'required_if:' . $onlyWhenField . ',runner' : 'required', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
        ];
    }

    public static function applicationMessages(): array
    {
        return [
            'portal_id.required' => 'Enter your UCLM portal ID.',
            'portal_id.required_if' => 'Enter your UCLM portal ID.',
            'portal_id.unique' => 'That portal ID is already used by another account.',
            'course.required' => 'Enter your course.',
            'course.required_if' => 'Enter your course.',
            'year_level.required' => 'Pick your year level.',
            'year_level.required_if' => 'Pick your year level.',
            'enrolled_confirmed.accepted' => 'Please confirm that you are a currently enrolled UCLM student.',
            'enrolled_confirmed.accepted_if' => 'Please confirm that you are a currently enrolled UCLM student.',
            'id_photo.required' => 'Upload a clear photo of your school ID.',
            'id_photo.required_if' => 'Upload a clear photo of your school ID.',
            'id_photo.mimes' => 'The school ID photo must be a JPG or PNG image.',
            'id_photo.max' => 'The school ID photo must be 4 MB or smaller.',
        ];
    }

    /** Save the application details on the user and mark them pending (admin approves later). */
    public static function saveApplication(User $user, array $data, UploadedFile $photo): void
    {
        if ($user->id_photo_path) {
            Storage::delete($user->id_photo_path); // replaced by the new photo
        }

        // private disk: the photo is only ever shown to the admin
        $path = $photo->store('runner-ids');

        $user->forceFill([
            'portal_id' => $data['portal_id'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'enrolled_confirmed' => true,
            'id_photo_path' => $path,
            'runner_status' => 'pending',
            'runner_applied_at' => now(),
        ])->save();
    }

    public function applyForm(Request $request)
    {
        $user = $request->user();

        if ($user->isRunner() || $user->hasPendingRunnerApplication()) {
            return redirect()->route('dashboard');
        }

        return view('runner-apply', ['user' => $user, 'yearLevels' => ProfileController::YEAR_LEVELS]);
    }

    /** A customer asks to become a runner. The admin approves it. */
    public function apply(Request $request)
    {
        $user = $request->user();

        if ($user->isRunner() || $user->hasPendingRunnerApplication()) {
            return redirect()->route('dashboard')->with('error', 'You already applied or you are already a runner.');
        }

        $data = $request->validate(self::applicationRules($user->id), self::applicationMessages());
        self::saveApplication($user, $data, $request->file('id_photo'));

        return redirect()->route('dashboard')->with('success', 'Application sent! The admin will check your school ID and review it. You will see Runner Jobs once you are approved.');
    }

    /** Runner Jobs: the job board (runners only). */
    public function board(Request $request)
    {
        $runner = $request->user();

        $available = Order::with('items', 'user')
            ->where('status', 'pending')
            ->whereNull('runner_id')
            ->where('user_id', '!=', $runner->id)
            ->oldest()
            ->get();

        $mine = $runner->runnerOrders()->with('items', 'user', 'photos')
            ->whereIn('status', ['preparing', 'on_the_way'])->oldest('accepted_at')->get();

        $done = $runner->runnerOrders()->with('items')
            ->where('status', 'delivered')->latest('delivered_at')->limit(10)->get();

        $earned = (float) $runner->runnerOrders()->where('status', 'delivered')->sum('delivery_fee');

        return view('jobs.board', [
            'available' => $available,
            'mine' => $mine,
            'done' => $done,
            'earned' => $earned,
            'deliveredCount' => $runner->runnerOrders()->where('status', 'delivered')->count(),
            'balance' => (float) $runner->wallet_balance,
            'committed' => $runner->walletCommitted(),
        ]);
    }

    public function accept(Request $request, Order $order)
    {
        return $this->act($request, $order, fn () => $order->acceptBy($request->user()),
            "You took {$order->order_number}. The customer can now see you're on it.");
    }

    public function release(Request $request, Order $order)
    {
        $this->mine($request, $order);

        return $this->act($request, $order, fn () => $order->releaseRunner(),
            "You gave back {$order->order_number}. It's open for other runners again.");
    }

    public function start(Request $request, Order $order)
    {
        $this->mine($request, $order);

        if ($error = $this->requirePhoto($request, $order, 'runner_pickup', 'Take a photo of the order you picked up before you set off.')) {
            return back()->with('error', $error);
        }

        return $this->act($request, $order, fn () => $order->startDelivery(), 'Nice! The customer sees that you are on the way.');
    }

    public function complete(Request $request, Order $order)
    {
        $this->mine($request, $order);

        if ($error = $this->requirePhoto($request, $order, 'runner_delivery', 'Take a photo that shows you delivered it before you mark it done.')) {
            return back()->with('error', $error);
        }

        $note = $order->payment_method === 'wallet'
            ? 'Delivered! Your delivery fee was added to your wallet.'
            : 'Delivered! The cash you owe UniFetch was taken from your wallet.';

        return $this->act($request, $order, fn () => $order->complete(), $note);
    }

    /** Saves the photo sent with the form (if any), then checks that the proof exists. Returns an error text or null. */
    private function requirePhoto(Request $request, Order $order, string $kind, string $missing): ?string
    {
        if ($request->hasFile('photo')) {
            $request->validate(['photo' => OrderPhoto::RULES], [
                'photo.image' => 'That file is not a photo.',
                'photo.mimes' => 'The photo must be a JPG, PNG or WebP image.',
                'photo.max' => 'The photo must be 6 MB or smaller.',
            ]);
            OrderPhoto::saveFor($order, $request->user(), $kind, $request->file('photo'));
        }

        return $order->photos()->where('kind', $kind)->exists() ? null : $missing;
    }

    private function mine(Request $request, Order $order): void
    {
        abort_unless($order->runner_id === $request->user()->id, 403);
    }

    private function act(Request $request, Order $order, \Closure $do, string $success)
    {
        try {
            $do();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $success);
    }
}
