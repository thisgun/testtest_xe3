<?php
/**
 * SettingsMiddleware class.
 *
 * PHP version 5
 *
 * @category    Settings
 * @package     Xpressengine\Settings
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Settings;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Xpressengine\Permission\Instance;
use Xpressengine\User\Models\Guest;
use Xpressengine\User\Rating;
use Xpressengine\Support\Exceptions\AccessDeniedHttpException;
use Xpressengine\Theme\ThemeHandler;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

/**
 * 이 클래스는 Xpressengine에서 route middleware로 작동한다.
 * 관리페이지에 접근하는 요청이 들어올 경우, 관리페이지용 테마를 적용시키고 권한을 검사하는 역할을 한다.
 *
 * @category    Settings
 * @package     Xpressengine\Settings
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class SettingsMiddleware
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var GateContract
     */
    protected $gate;

    /**
     * 생성자이며, Application을 주입받는다.
     *
     * @param Application  $app  Application
     * @param GateContract $gate GateContract
     */
    public function __construct(Application $app, GateContract $gate)
    {
        $this->app = $app;
        $this->gate = $gate;
    }

    /**
     * route middleware에서 호출되는 메소드이며, 현재 Request가 관리페이지에 접근하는 요청인지 판단한다.
     * 관리페이지의 요청일 경우 관리페이지 테마를 적용하고, 접근권한이 있는지 체크한다.
     *
     * @param  \Illuminate\Http\Request $request current request
     * @param  \Closure                 $next    next middleware
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check permission
        $this->checkPermission($request);

        // apply theme
        $this->applySettingsTheme();

        return $next($request);
    }

    /**
     * 현재 요청에 대한 권한이 있는지 검사한다. Guest일 경우 무조건 권한없음으로 처리하며, 최고관리자인 경우 무조건 통과시킨다.
     *
     * @param Request $request current request
     *
     * @throws \Xpressengine\Permission\Exceptions\NotSupportedException
     * @return void
     */
    protected function checkPermission(Request $request)
    {
        $user = $request->user();
        if ($user instanceof Guest) {
            throw new AccessDeniedHttpException();
        }

        if ($user->getRating() === Rating::SUPER) {
            return;
        }

        $route = $request->route();
        if($route->getName() === 'settings.dashboard' && $user->getRating() === Rating::MANAGER) {
            return;
        }

        $permissionId = array_get($route->getAction(), 'permission');

        if ($permissionId === null) {
            throw new AccessDeniedHttpException();
        }

        if ($this->gate->denies('access', new Instance('settings.'.$permissionId))) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * 관리페이지 테마를 지정한다.
     *
     * @return void
     */
    protected function applySettingsTheme()
    {
        $config = $this->app['config'];

        /** @var ThemeHandler $themeHandler */
        $themeHandler = $this->app['xe.theme'];

        $theme = $config->get('xe.settings.theme');

        $themeHandler->selectTheme($theme);
    }
}
