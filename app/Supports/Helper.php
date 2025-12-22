<?php

namespace App\Supports;

class Helper
{
    public static function syncBuiltInPermissions(): array
    {
        $permissions = config('permission.built_in_permissions');
        
        $result = [];
        foreach ($permissions as $permission) {
            $result[] = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
        
        return $result;
    }
    
    public static function encrypt(string $plain): string
    {
        // Ambil APP_KEY Laravel (tanpa base64:)
        $appKey = config('app.key');
        
        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }
        
        // Compress
        $compressed = gzencode($plain, 9);
        
        // Derive key & IV
        $key = hash('sha256', $appKey, true); // 32 byte
        $iv  = substr($key, 0, 16);           // 16 byte (pendek & konsisten)
        
        // Encrypt (RAW binary)
        $encrypted = openssl_encrypt(
            $compressed,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }
    
    public static function decrypt(string $encrypted): string
    {
        try {
            $appKey = config('app.key');
            
            if (str_starts_with($appKey, 'base64:')) {
                $appKey = base64_decode(substr($appKey, 7));
            }
            
            $key = hash('sha256', $appKey, true);
            $iv  = substr($key, 0, 16);
            
            $encrypted = base64_decode(strtr($encrypted, '-_', '+/'));
            
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            return gzdecode($decrypted);
        } catch (\Exception $e) {
            return '';
        }
    }
}