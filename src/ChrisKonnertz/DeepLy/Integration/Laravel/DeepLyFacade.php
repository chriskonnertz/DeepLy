<?php

namespace ChrisKonnertz\DeepLy\Integration\Laravel;

use Illuminate\Support\Facades\Facade;

class DeepLyFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'deeply';
    }

}