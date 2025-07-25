<?php

namespace Hwkdo\HwkAdminLaravel\Commands;

use Illuminate\Console\Command;

class HwkAdminLaravelCommand extends Command
{
    public $signature = 'hwk-admin-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
