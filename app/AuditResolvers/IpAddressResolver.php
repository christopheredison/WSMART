<?php

namespace App\AuditResolvers;

use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class IpAddressResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null): ?string
    {
        $ip = $auditable->preloadedResolverData['ip_address'] ?? Request::ip();

        if ($ip === null || $ip === '') {
            return null;
        }

        return $ip;
    }
}
