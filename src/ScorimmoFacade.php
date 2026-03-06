<?php

namespace CLDT\Scorimmo;

use Illuminate\Support\Facades\Facade;

class ScorimmoFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'scorimmo';
    }
}
