<?php
/**
 * Config service provider class
 *
 * PHP version 5
 *
 * @category    Config
 * @package     Xpressengine\Config
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Xpressengine\Config\ConfigManager;
use Xpressengine\Config\Validator;
use Xpressengine\Support\LaravelCache;
use Xpressengine\Config\Repositories\DatabaseRepository;
use Xpressengine\Config\Repositories\CacheDecorator;

/**
 * laravel 에서 사용을 위해 패키지를 등록하는 역할을 하는 클래스
 *
 * @category    Config
 * @package     Xpressengine\Config
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * executed when application booted
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(['xe.config' => ConfigManager::class], function ($app) {
            $repo = new DatabaseRepository($app['xe.db']->connection());
            
            if ($app['config']['app.debug'] !== true) {
                $repo = new CacheDecorator(
                    $repo,
                    new LaravelCache($app['cache.store'])
                );
            }

            $proxyClass = $app['xe.interception']->proxy(ConfigManager::class, 'XeConfig');
            $configManager = new $proxyClass($repo, new Validator($app['validator']));
            return $configManager;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('xe.config');
    }
}
