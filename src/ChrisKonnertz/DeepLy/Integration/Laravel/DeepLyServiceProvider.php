<?php

namespace ChrisKonnertz\DeepLy\Integration\Laravel;

use ChrisKonnertz\DeepLy\DeepLy;
use Illuminate\Support\ServiceProvider;

class DeepLyServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('deeply', function()
        {
            return new DeepLy();
        });
    }

}