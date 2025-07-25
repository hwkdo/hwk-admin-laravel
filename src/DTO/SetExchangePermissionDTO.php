<?php

namespace Hwkdo\HwkAdminLaravel\DTO;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class SetExchangePermissionDTO extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public string $owner_upn,
        public string $delegate_upn,
        public string $accessRights, #FullAccess, ReadPermission
        public string $action, #Add, Remove
        
    ) {}
}
