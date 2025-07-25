<?php

namespace Hwkdo\HwkAdminLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

// use Hwkdo\HwkAdminLaravel\Commands\HwkAdminLaravelCommand;

class HwkAdminLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('hwk-admin-laravel')
            ->hasConfigFile();
        // ->hasViews()
        // ->hasMigration('create_hwk_admin_laravel_table')
        // ->hasCommand(HwkAdminLaravelCommand::class);
    }
}
