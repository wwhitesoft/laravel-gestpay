<?php

/**
 *
 * Biscolab Laravel Gestpay - helpers.php file
 * web : robertobelotti.com, github.com/biscolab
 *
 * @author author: Roberto Belotti - info@robertobelotti.com
 * @license MIT License @ https://github.com/biscolab/laravel-gestpay/blob/master/LICENSE
 */

if (!function_exists('gestpay')) {
    /**
     * @return Biscolab\ReCaptcha\ReCaptchaBuilder
     */
    function gestpay()
    {
        return app('gestpay');
    }
}