<?php

namespace HermesTecnologia\Analyser\Facades;

use Illuminate\Support\Facades\Facade;

class Analyser extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'analyser';
    }
}
