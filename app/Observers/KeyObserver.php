<?php

namespace App\Observers;

use App\Models\Key;

class KeyObserver
{
    /**
     * Listen to the Key creating event (for logging).
     */
    public function creating(Key $key)
    {
        // Set the created_by if not already set
        if (is_null($key->created_by)) {
            $key->created_by = auth()->id();
        }
    }

    /**
     * Listen to the Key created event and log it.
     */
    public function created(Key $key)
    {
        $key->logCreate();
    }

    /**
     * Listen to the Key updating event and log field changes.
     */
    public function updating(Key $key)
    {
        $changed = [];

        foreach ($key->getRawOriginal() as $col => $value) {
            if ($col === 'updated_at') {
                continue;
            }
            if ($key->getRawOriginal()[$col] != $key->getAttributes()[$col]) {
                $changed[$col]['old'] = $key->getRawOriginal()[$col];
                $changed[$col]['new'] = $key->getAttributes()[$col];
            }
        }

        if (count($changed) > 0) {
            $log = new \App\Models\Actionlog;
            $log->item_type = Key::class;
            $log->item_id = $key->id;
            $log->created_at = date('Y-m-d H:i:s');
            $log->created_by = auth()->id();
            $log->log_meta = json_encode($changed);
            $log->logaction('update');
        }
    }
}
