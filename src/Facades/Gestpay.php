<?php

/**
 *
 * Biscolab Laravel Gestpay - Gestpay Class
 * web : robertobelotti.com, github.com/biscolab
 *
 * @package Biscolab\Gestpay
 * @subpackage Facades
 * @author author: Roberto Belotti - info@robertobelotti.com
 * @license MIT License @ https://github.com/biscolab/laravel-gestpay/blob/master/LICENSE
 */

namespace Biscolab\Gestpay\Facades;

use Illuminate\Support\Facades\Facade;

class Gestpay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gestpay';
    }
}
