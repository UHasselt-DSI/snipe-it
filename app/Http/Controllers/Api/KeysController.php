<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\KeysTransformer;
use App\Models\Key;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KeysController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view', Key::class);

        $allowed_columns = [
            'id',
            'key_id',
            'location_id',
            'assigned_to',
            'status',
            'notes',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $keys = Key::select('keys.*')
            ->with('location', 'assignedTo', 'createdBy');

        if ($request->filled('filter') || $request->filled('search')) {
            $keys->TextSearch($request->input('filter') ?: $request->input('search'));
        }

        if ($request->filled('location_id')) {
            $keys->where('keys.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('assigned_to')) {
            $keys->where('keys.assigned_to', '=', $request->input('assigned_to'));
        }

        if ($request->filled('status')) {
            $keys->where('keys.status', '=', $request->input('status'));
        }

        // Make sure the offset and limit are actually integers
        $total = $keys->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override = $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'location':
                $keys = $keys->OrderLocation($order);
                break;
            case 'assigned_to':
                $keys = $keys->OrderAssignedTo($order);
                break;
            default:
                $keys = $keys->orderBy($column_sort, $order);
                break;
        }

        $keys = $keys->skip($offset)->take($limit)->get();

        return (new KeysTransformer)->transformKeys($keys, $total);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Key::class);

        $key = new Key;
        $key->key_id = $request->input('key_id');
        $key->location_id = $request->input('location_id');
        $key->notes = $request->input('notes');
        $key->created_by = auth()->id();

        if ($request->filled('assigned_to')) {
            $key->assigned_to = $request->input('assigned_to');
            $key->status = 'checked_out';
        } else {
            $key->status = 'available';
        }

        if ($key->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $key, trans('admin/keys/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $key->getErrors()));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorize('view', Key::class);
        $key = Key::findOrFail($id);

        return (new KeysTransformer)->transformKey($key);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update', Key::class);
        $key = Key::findOrFail($id);

        $key->key_id = $request->input('key_id');
        $key->location_id = $request->input('location_id');
        $key->notes = $request->input('notes');

        if ($request->filled('assigned_to')) {
            $key->assigned_to = $request->input('assigned_to');
            $key->status = 'checked_out';
        } else {
            $key->assigned_to = null;
            $key->status = 'available';
        }

        if ($key->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $key, trans('admin/keys/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $key->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('delete', Key::class);
        $key = Key::findOrFail($id);

        if ($key->status === 'checked_out') {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/keys/general.delete_disabled')));
        }

        $key->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/keys/message.delete.success')));
    }

    /**
     * Checkout a key to a user.
     */
    public function checkout(Request $request, $keyId)
    {
        $this->authorize('checkout', Key::class);
        $key = Key::findOrFail($keyId);

        if ($key->status === 'checked_out') {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/keys/message.checkout.already_checked_out')));
        }

        $assignedTo = $request->input('assigned_to');
        $target = User::find($assignedTo);

        if (is_null($target)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/keys/message.checkout.user_does_not_exist')));
        }

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
            'user_id' => auth()->id(),
            'note' => $request->input('note'),
            'remote_ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
        $log->logaction(\App\Enums\ActionType::Checkout);

        return response()->json(Helper::formatStandardApiResponse('success', $key, trans('admin/keys/message.checkout.success')));
    }

    /**
     * Check in a key.
     */
    public function checkin(Request $request, $keyId)
    {
        $this->authorize('checkin', Key::class);
        $key = Key::findOrFail($keyId);

        if ($key->status === 'available') {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/keys/message.checkin.already_available')));
        }

        $checkedOutTo = $key->assignedTo;

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
            'user_id' => auth()->id(),
            'note' => $request->input('note'),
            'remote_ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
        $log->logaction(\App\Enums\ActionType::CheckinFrom);

        return response()->json(Helper::formatStandardApiResponse('success', $key, trans('admin/keys/message.checkin.success')));
    }

    /**
     * Get history for a key.
     */
    public function history(Request $request, Key $key): JsonResponse|array
    {
        $this->authorize('view', Key::class);
        $historyQuery = $key->getHistory($request);
        $total = (clone $historyQuery)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $history = (clone $historyQuery)->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($history, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);
    }
}
