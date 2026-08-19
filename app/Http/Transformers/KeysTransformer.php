<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Key;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class KeysTransformer
{
    public function transformKeys(Collection $keys, $total)
    {
        $array = [];
        foreach ($keys as $key) {
            $array[] = self::transformKey($key);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformKey(Key $key)
    {
        $array = [
            'id' => $key->id,
            'key_id' => ($key->key_id) ? e($key->key_id) : null,
            'location' => ($key->location) ? [
                'id' => $key->location->id,
                'name' => e($key->location->name),
                'tag_color' => ($key->location->tag_color) ? e($key->location->tag_color) : null,
            ] : null,
            'assigned_to' => ($key->assignedTo) ? [
                'id' => $key->assignedTo->id,
                'name' => e($key->assignedTo->display_name),
                'first_name' => e($key->assignedTo->first_name),
                'last_name' => e($key->assignedTo->last_name),
                'username' => e($key->assignedTo->username),
                'display_name' => e($key->assignedTo->display_name),
                'email' => e($key->assignedTo->email),
            ] : null,
            'status' => $key->status,
            'notes' => ($key->notes) ? Helper::parseEscapedMarkedownInline($key->notes) : null,
            'created_by' => ($key->createdBy) ? [
                'id' => (int) $key->createdBy->id,
                'name' => e($key->createdBy->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($key->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($key->updated_at, 'datetime'),
        ];

        $permissions_array = [];
        $permissions_array['available_actions'] = [
            'checkout' => $key->isAvailable() && Gate::allows('checkout', Key::class),
            'checkin' => $key->isCheckedOut() && Gate::allows('checkin', Key::class),
            'update' => Gate::allows('update', Key::class),
            'delete' => $key->isAvailable() && Gate::allows('delete', Key::class),
        ];

        $permissions_array['user_can_checkout'] = $key->isAvailable();

        $array += $permissions_array;

        return $array;
    }
}
