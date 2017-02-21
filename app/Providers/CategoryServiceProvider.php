<?php
/**
 * This file is category service provier for laravel.
 *
 * PHP version 5
 *
 * @category    Category
 * @package     Xpressengine\Category
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Xpressengine\Category\CategoryHandler;
use Xpressengine\Category\CategoryItemRepository;
use Xpressengine\Category\CategoryRepository;

/**
 * 라라벨에서의 사용을 위한 서비스 제공자.
 *
 * @category    Category
 * @package     Xpressengine\Category
 */
class CategoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
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
        $this->app->singleton(['xe.category' => CategoryHandler::class], function ($app) {
            $proxyClass = $app['xe.interception']->proxy(CategoryHandler::class, 'XeCategory');

            return new $proxyClass;
        }, true);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['xe.category'];
    }
}
