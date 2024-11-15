<?php

namespace AdemtiApps\Supersonic;

use Statamic\Providers\AddonServiceProvider;

/**
 * Copyright (c) Ademti Software Ltd. // www.ademti-software.co.uk
 *
 * Released under the PolyForm Perimeter License 1.0.0
 * See LICENSE.md for details.
 */
class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/supersonic.js',
            'resources/scss/supersonic.scss'
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $publishAfterInstall = true;
}
