<?php

// config for Hwkdo/HwkAdminLaravel
return [
    'url' => env('HWK_ADMIN_URL', 'https://admin.localhost.test'),
    'token' => env('HWK_ADMIN_TOKEN'),

    /*
     * false: TLS-Zertifikat nicht prüfen (interne CA, fehlendes CA-Bundle in Container/WSL).
     * true: normale Zertifikatsprüfung (PHP openssl.cafile / System-CAs).
     */
    'verify_ssl' => filter_var(env('HWK_ADMIN_VERIFY_SSL', 'false'), FILTER_VALIDATE_BOOLEAN),
];
