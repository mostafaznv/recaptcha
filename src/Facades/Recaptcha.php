<?php

namespace Mostafaznv\Recaptcha\Facades;

use Illuminate\Support\Facades\Facade;
use Mostafaznv\Recaptcha\Recaptcha as RecaptchaInstance;

/**
 * Recaptcha Facade
 *
 * @package Mostafaznv\Recaptcha\Facades
 * @see RecaptchaInstance
 */
class Recaptcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RecaptchaInstance::class;
    }
}