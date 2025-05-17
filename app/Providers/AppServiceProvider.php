<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use App\Extensions\MirroringLocalFilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('mirrored', function ($app, $config) {
            $adapter = new MirroringLocalFilesystemAdapter(storage_path('app'));
            return new Filesystem($adapter);
        });
    }
}
