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
 * @author Administrator
 */
class Config {

    private static $_data = array();

    public static function load($name) {
        if (!array_key_exists($name, static::$_data)) {
            $configFileName = config_path() . '/' . $name . '.php';
            if (is_file($configFileName)) {
                static::$_data[$name] = include $configFileName;
            }
        }
        return static::$_data[$name];
    }

    public static function all() {
        return static::$_data;
    }

    public static function get($name, $default = NULL) {
        $names = explode('.', $name);
        if (isset($names[0]) && !Arr::has(static::$_data, $names[0])) {
            static::load($names[0]);
        }
        return Arr::get(static::$_data, $name, $default);
    }

    public static function set($name, $value = array()) {
        Arr::set(static::$_data, $name, $value);
    }

    public static function has($name) {
        return Arr::has(static::$_data, $name);
    }

    public static function remove($name) {
        Arr::set(static::$_data, $name, array());
    }

}
