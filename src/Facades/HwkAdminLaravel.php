<?php

namespace Hwkdo\HwkAdminLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hwkdo\HwkAdminLaravel\HwkAdminLaravel
 */
class HwkAdminLaravel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hwkdo\HwkAdminLaravel\HwkAdminLaravel::class;
    }
}
