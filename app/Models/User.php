<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    public static $cacheSessionKeyPrefix = 'user_';

    public static $cacheSessionKeySuffix = '_session_key:';

    protected $fillable = [
        'openid', 'nick_name', 'avatar_url',
        'gender', 'city', 'province', 'country',
        'is_manager', 'store_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
