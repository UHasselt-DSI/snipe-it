<?php

namespace App\Http\Controllers\Keys;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Key;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KeyCheckinController extends Controller
{
    /**
     * Show the form to check in a key.
     */
    public function create(Key $key): View|RedirectResponse
    {
        $this->authorize('checkin', Key::class);

        if ($key->isAvailable()) {
            return redirect()->route('keys.index')
                ->with('error', trans('admin/keys/message.checkin.already_available'));
        }

        return view('keys.checkin', compact('key'));
    }

    /**
     * Check in the key so it is available again.
     */
    public function store(Request $request, Key $key): RedirectResponse
    {
        $this->authorize('checkin', Key::class);

        if ($key->isAvailable()) {
            return redirect()->back()
                ->with('error', trans('admin/keys/message.checkin.already_available'));
        }

        $checkedOutTo = $key->assignedTo;

        // Update the key
        $key->assigned_to = null;
        $key->status = 'available';
        $key->save();

        // Log the checkin
        $log = \App\Models\Actionlog::create([
            'item_type' => Key::class,
            'item_id' => $key->id,
            'target_type' => ($checkedOutTo) ? User::class : null,
            'target_id' => ($checkedOutTo) ? $checkedOutTo->id : null,
            'action_date' => now(),
            'created_by' => auth()->id(),
            'note' => $request->input('note'),
            'remote_ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
        $log->logaction(\App\Enums\ActionType::CheckinFrom);

        session()->put([
            'redirect_option' => $request->input('redirect_option'),
        ]);

        return Helper::getRedirectOption($request, $key->id, 'Keys')
            ->with('success', trans('admin/keys/message.checkin.success'));
    }
}
