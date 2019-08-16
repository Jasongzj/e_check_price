<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }
}
