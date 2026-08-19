<?php

namespace App\Policies;

use App\Models\User;

class KeyPolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'keys';
    }
}
