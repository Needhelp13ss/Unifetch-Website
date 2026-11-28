<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Proof photos: shop handover, runner pickup, runner delivery, customer received. */
class OrderPhotoController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $order->load('items', 'photos');
        $user = $request->user();
        abort_unless($order->canBeSeenBy($user) && !$user->isAdmin(), 403);

        $data = $request->validate([
            'kind' => ['required', 'in:' . implode(',', array_keys(OrderPhoto::KINDS))],
            'photo' => OrderPhoto::RULES,
        ], [
            'photo.required' => 'Take or choose a photo first.',
            'photo.image' => 'That file is not a photo.',
            'photo.mimes' => 'The photo must be a JPG, PNG or WebP image.',
            'photo.max' => 'The photo must be 6 MB or smaller.',
        ]);

        $kind = $data['kind'];
        $allowed = $order->photoAllowedFor($user, $kind);

        if (!$allowed) {
            return back()->with('error', 'You can’t add that photo to this order right now.');
        }

        OrderPhoto::saveFor($order, $user, $kind, $request->file('photo'));

        return back()->with('success', 'Photo saved as proof.');
    }

    public function show(Request $request, Order $order, OrderPhoto $photo)
    {
        $order->load('items');
        abort_unless($photo->order_id === $order->id && $order->canBeSeenBy($request->user()), 403);
        abort_unless(Storage::exists($photo->path), 404);

        return Storage::response($photo->path);
    }
}
