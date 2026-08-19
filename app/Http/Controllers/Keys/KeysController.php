<?php

namespace App\Http\Controllers\Keys;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Key;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * This controller handles all actions related to Keys for
 * the Snipe-IT Asset Management application.
 */
class KeysController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the keys listing.
     */
    public function index(): View
    {
        $this->authorize('index', Key::class);

        return view('keys.index');
    }

    /**
     * Returns a view with a form to create a new Key.
     */
    public function create(): View
    {
        $this->authorize('create', Key::class);

        return view('keys.edit')->with('item', new Key);
    }

    /**
     * Validate and save new Key from form post.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Key::class);

        $key = new Key;
        $key->key_id = $request->input('key_id');
        $key->location_id = $request->input('location_id');
        $key->notes = $request->input('notes');
        $key->status = 'available';
        $key->created_by = auth()->id();

        if ($request->filled('assigned_to')) {
            $key->assigned_to = $request->input('assigned_to');
            $key->status = 'checked_out';
        }

        if ($key->save()) {            if ($request->input('redirect_option') === 'back') {
                session()->put(['redirect_option' => 'index']);
            } else {
                session()->put(['redirect_option' => $request->input('redirect_option')]);
            }

            return Helper::getRedirectOption($request, $key->id, 'Keys')
                ->with('success', trans('admin/keys/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($key->getErrors());
    }

    /**
     * Return view for the Key update form, prepopulated with existing data.
     */
    public function edit(Key $key): View|RedirectResponse
    {
        $this->authorize('update', $key);

        if ($safeReferer = Helper::sameOriginUrl(url()->previous())) {
            session()->put('url.intended', $safeReferer);
        }

        return view('keys.edit')->with('item', $key);
    }

    /**
     * Save edited Key from form post.
     */
    public function update(Request $request, Key $key): RedirectResponse
    {
        $this->authorize('update', $key);

        $key->key_id = $request->input('key_id');
        $key->location_id = $request->input('location_id');
        $key->notes = $request->input('notes');

        // Only update assigned_to if explicitly submitted from the form
        if ($request->filled('assigned_to')) {
            $key->assigned_to = $request->input('assigned_to');
            $key->status = 'checked_out';
        } elseif ($request->has('assigned_to')) {
            // Field was present but empty — explicitly check in
            $key->assigned_to = null;
            $key->status = 'available';
        }
        // else: field not in form, keep existing assignment/status

        if ($key->save()) {
            if ($request->input('redirect_option') === 'back') {
                session()->put(['redirect_option' => 'index']);
            } else {
                session()->put(['redirect_option' => $request->input('redirect_option')]);
            }

            return Helper::getRedirectOption($request, $key->id, 'Keys')
                ->with('success', trans('admin/keys/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($key->getErrors());
    }

    /**
     * Delete the given key.
     */
    public function destroy(Key $key): RedirectResponse
    {
        $this->authorize('delete', $key);

        if ($key->isAvailable()) {
            $key->delete();

            return redirect()->route('keys.index')->with('success', trans('admin/keys/message.delete.success'));
        }

        return redirect()->route('keys.index')->with('error', trans('admin/keys/general.delete_disabled'));
    }

    /**
     * Returns a view that presents the key detail view.
     */
    public function show(Key $key): View|RedirectResponse
    {
        $this->authorize('view', $key);

        return view('keys.view', compact('key'));
    }
}
