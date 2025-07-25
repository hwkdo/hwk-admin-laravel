<?php

namespace Hwkdo\HwkAdminLaravel\DTO;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class SetExchangeQuotaDTO extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public string $owner_upn,
        public string $ProhibitSendQuota, #XX.xGB
        public string $ProhibitSendReceiveQuota, #XX.xGB
        public string $IssueWarningQuota, #XX.xGB
        
    ) {}
}
