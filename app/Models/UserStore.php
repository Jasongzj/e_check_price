<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserStore extends Pivot
{
    public $incrementing = true;
}
