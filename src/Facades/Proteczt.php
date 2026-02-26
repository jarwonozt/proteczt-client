<?php

namespace Tecnozt\Proteczt\Facades;

use Illuminate\Support\Facades\Facade;
use Tecnozt\Proteczt\ProtecztLicenseService;

/**
 * @method static bool register()
 * @method static array checkStatus()
 * @method static array refreshStatus()
 * @method static bool isActive()
 *
 * @see \Tecnozt\Proteczt\ProtecztLicenseService
 */
class Proteczt extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ProtecztLicenseService::class;
    }
}
