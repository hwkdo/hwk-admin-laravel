<?php

namespace Hwkdo\HwkAdminLaravel\DTO;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class OcrOutputDTO extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public bool $success,
        public ?string $data,
    ) {}
}
