<?php

/*
 * Copyright (C) 2014 Allen Niu <h@h1soft.net>

 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.



 * This file is part of the hmvc package.
 * (w) http://www.hmvc.cn
 * (c) Allen Niu <h@h1soft.net>

 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.


 */

namespace hmvc\Core;

use hmvc\Helpers\Arr;

/**
 * Description of Config
 *
 * @author allen <allen@w4u.cn>
 */
class Config {

    /**
     *
     * @var array Config Item
     */
    private static $data = array();

    /**
     * 
     * @param type $name
     * @return array
     */
    public static function load($name) {
        if (!array_key_exists($name, static::$data)) {
            $configFileName = config_path() . '/' . $name . '.php';
            if (is_file($configFileName)) {
                static::$data[$name] = include $configFileName;
            }
        }
    }

    /**
     * 
     * @return array
     */
    public static function all() {
        return static::$data;
    }

    /**
     * 
     * @param string $name
     * @param array|string $default
     * @return array|string
     */
    public static function get($name, $default = NULL) {
        $names = explode('.', $name);
        if (isset($names[0]) && !Arr::has(static::$data, $names[0])) {
            static::load($names[0]);
        }
        return Arr::get(static::$data, $name, $default);
    }

    /**
     * 
     * @param string $name
     * @param array|string $value
     * @return \hmvc\Core\Config
     */
    public static function set($name, $value = array()) {
        $names = explode('.', $name);
        if (isset($names[0]) && !Arr::has(static::$data, $names[0])) {
            static::load($names[0]);
        }
        Arr::set(static::$data, $name, $value);
    }

    /**
     * 
     * @param string $name
     * @return array|string
     */
    public static function has($name) {
        return Arr::has(static::$data, $name);
    }

    /**
     * Remove Config Item
     * @param string $name
     * @return \hmvc\Core\Config
     */
    public static function remove($name) {
        Arr::set(static::$data, $name, array());
    }

}
