<?php

namespace App\Http\Controllers\Keys;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Key;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KeyCheckoutController extends Controller
{
    /**
     * Return the form to checkout a Key to a user.
     */
    public function create(Key $key): View|RedirectResponse
    {
        $this->authorize('checkout', Key::class);

        if ($key->isCheckedOut()) {
            return redirect()->route('keys.index')
                ->with('error', trans('admin/keys/message.checkout.already_checked_out'));
        }

        return view('keys.checkout', compact('key'));
    }

    /**
     * Save the Key checkout information.
     */
    public function store(Request $request, Key $key): RedirectResponse
    {
        $this->authorize('checkout', Key::class);

        if ($key->isCheckedOut()) {
            return redirect()->back()
                ->with('error', trans('admin/keys/message.checkout.already_checked_out'));
        }

        $assignedTo = $request->input('assigned_to');

        if (is_null($assignedTo)) {
            return redirect()->back()
                ->with('error', trans('admin/keys/message.checkout.no_user_selected'));
        }

        $target = User::find($assignedTo);
        if (is_null($target)) {
            return redirect()->back()
                ->with('error', trans('admin/keys/message.checkout.user_does_not_exist'));
        }

        // Update the key
        $key->assigned_to = $target->id;
        $key->status = 'checked_out';
        $key->save();

        // Log the checkout
        $log = \App\Models\Actionlog::create([
            'item_type' => Key::class,
            'item_id' => $key->id,
            'target_type' => User::class,
            'target_id' => $target->id,
            'action_date' => now(),
            'created_by' => auth()->id(),
            'note' => $request->input('note'),
            'remote_ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
        $log->logaction(\App\Enums\ActionType::Checkout);

        session()->put([
            'redirect_option' => $request->input('redirect_option'),
        ]);

        return Helper::getRedirectOption($request, $key->id, 'Keys')
            ->with('success', trans('admin/keys/message.checkout.success'));
    }
}
