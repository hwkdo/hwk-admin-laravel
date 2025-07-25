<?php

namespace Hwkdo\HwkAdminLaravel\DTO;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class GetExchangePermissionOutputDTO extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public string $InheritanceType,
        public string $User,
        public array $AccessRights,        
    ) {}
}
