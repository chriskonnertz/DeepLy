<?php

namespace ChrisKonnertz\DeepLy\Integrations\Laravel;

use ChrisKonnertz\DeepLy\DeepLy;
use Illuminate\Support\ServiceProvider;

class DeepLyServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('deeply', function()
        {
            // Create a new DeepLy instance with the API key from .env file
            return new DeepLy(env('DEEPL_API_KEY'));
        });
    }

}