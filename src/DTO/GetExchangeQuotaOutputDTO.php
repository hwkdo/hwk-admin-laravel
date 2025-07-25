<?php

namespace Hwkdo\HwkAdminLaravel\DTO;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class GetExchangeQuotaOutputDTO extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public ?string $ProhibitSendQuota = null, #XX.xGB
        public ?string $ProhibitSendReceiveQuota = null, #XX.xGB
        public ?string $IssueWarningQuota = null, #XX.xGB
        
    ) {}
}
