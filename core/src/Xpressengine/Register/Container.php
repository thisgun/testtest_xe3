<?php
/**
 * Container class. This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category    Register
 * @package     Xpressengine\Register
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Register;

use Xpressengine\Register\Exceptions\ValueMustBeArrayException;

/**
 * 이 클래스는 Key Value 의 저장소를 제공합니다.
 * 인터페이스 구성은 Illuminate\Config\Repository 와 유사합니다.
 *
 * 내부 아이템을 구성하기 위해서 Illuminate\Support\Arr 을 사용합니다.
 * 이에 따라 key 를 구분할 때 dot(점, '.') 을 사용합니다.
 * key 구성은 Illuminate config 사용에 대해서 검색하세요.
 *
 * Register 는 CoreRegister, PluginRegister, PluginRegistryManager 를 통해서 사용됩니다.
 *
 * ## 사용
 *
 * ### set()
 * * key 를 이용해서 value 를 등록합니다.
 * ```php
 * Register->set('key', $mixedValue);
 * ```
 * > 'key' 이미 있는경우 Exception 이 발생합니다.
 *
 * * key 를 array 로 사용
 * ```php
 * Register->set([
 * 'key1' => $mixedValue1,
 * 'key2' => $mixedValue2,
 * ]);
 * ```
 * > key 유무에 상관없이 새로 등록됩니다.
 *
 * ### add()
 * * key 를 이용해서 value 를 등록합니다.
 * ```php
 * Register->set('key', $mixedValue);
 * ```
 *  > 'key' 가 없ㅇ면Exception 이 발생합니다.
 *
 * ### has()
 * * key 가 있는지 체크합니다.
 *
 * ### get()
 * * 'key' 의 정보를 반환합니다.
 *
 *
 * @category    Register
 * @package     Xpressengine\Register
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class Container implements RegisterInterface
{
    /**
     * Register 에서 array 를 처리하기 위한 class 이름
     * 기본으로 Illuminate\Support\Arr 을 사용한다.
     *
     * @var \Illuminate\Support\Arr
     */
    protected $arrClass;

    /**
     * @var array registered items
     */
    protected $items = [];

    /**
     * @param \Illuminate\Support\Arr $arrClass array class name
     */
    public function __construct($arrClass)
    {
        $this->arrClass = $arrClass;
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key key
     *
     * @return bool
     */
    public function has($key)
    {
        $class = $this->arrClass;
        return $class::has($this->items, $key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string $key     key
     * @param  mixed  $default default value
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $class = $this->arrClass;
        return $class::get($this->items, $key, $default);
    }

    /**
     * 주어진 key에 value를 지정한다. 이미 지정된 value가 있다면 덮어씌운다.
     *
     * @param  array|string $key   key
     * @param  mixed        $value value for setting
     *
     * @return void
     */
    public function set($key, $value = null)
    {
        $class = $this->arrClass;

        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $class::set($this->items, $innerKey, $innerValue);
            }
        } else {
            $class::set($this->items, $key, $value);
        }
    }

    /**
     * 주어진 key에 value를 추가한다.
     * 만약 해당 key에 지정된 value가 이미 있을 경우, 아무 행동도 하지 않는다.
     *
     * @param  array|string $key   key
     * @param  mixed        $value value for adding
     *
     * @return void
     */
    public function add($key, $value)
    {
        $class = $this->arrClass;
        $this->items = $class::add($this->items, $key, $value);
    }

    /**
     * 주어진 키에 해당하는 array의 제일 앞에 value를 추가한다.
     * 만약 주어진 키에 해당하는 array가 없다면 array를 생성후 추가한다.
     *
     * @param  string $key   key
     * @param  mixed  $value value for prepend
     *
     * @return void
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key, []);

        if (!is_array($array)) {
            throw new ValueMustBeArrayException();
        }

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * 주어진 키에 해당하는 array의 제일 뒤에 value를 추가한다.
     * 만약 주어진 키에 해당하는 array가 없다면 array를 생성후 추가한다.
     *
     *
     *
     *
     * Push a value onto an array configuration value.
     *
     * @param  string $key   key
     * @param  mixed  $id    pushed data's id
     * @param  mixed  $value pushed data's value
     *
     * @return void
     */
    public function push($key, $id, $value = null)
    {
        $array = $this->get($key, []);

        if (!is_array($array)) {
            throw new ValueMustBeArrayException();
        }

        if ($value === null) {
            $value = $id;
            $array[] = $value;
        } else {
            $array[$id] = $value;
        }

        $class = $this->arrClass;
        $this->items = $class::set($this->items, $key, $array);
    }

    /**
     * 등록된 모든 아이템을 조회한다.
     *
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string $key key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string $key key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param string $key   key
     * @param mixed  $value value for setting
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string $key key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}
