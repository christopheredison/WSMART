<?php

namespace App\Resolvers;

use OwenIt\Auditing\Contracts\Resolver;
use Illuminate\Support\Facades\Request;

class IpAddressResolver implements Resolver
{
    public static function resolve(): ?string
    {
        $ip = Request::ip();
        // Jika kosong, kembalikan null agar PostgreSQL tidak error
        return empty($ip) ? null : $ip; 
    }
}