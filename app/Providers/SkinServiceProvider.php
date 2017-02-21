<?php
/**
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Xpressengine\Skin\SkinHandler;
use Xpressengine\Skin\SkinInstanceStore;

class SkinServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            ['xe.skin' => SkinHandler::class],
            function ($app) {
                $skinInstanceStore = new SkinInstanceStore($app['xe.config']);
                $defaultSkins = $app['config']->get('xe.skin.defaultSkins');
                $defaultSettingsSkins = $app['config']->get('xe.skin.defaultSettingsSkins');
                $skinHandler = $app['xe.interception']->proxy(SkinHandler::class, 'XeSkin');
                $skinHandler = new $skinHandler(
                    $app['xe.pluginRegister'],
                    $skinInstanceStore,
                    $defaultSkins,
                    $defaultSettingsSkins
                );
                return $skinHandler;
            }
        );
    }

    public function boot()
    {
        $this->app['xe.pluginRegister']->add(\App\Skins\Error\DefaultErrorSkin::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}
