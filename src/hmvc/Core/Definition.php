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

/**
 * Description of Definition
 *
 * @author Administrator
 */
final class Definition {

    private static $definitions = array(
        'session' => 'hmvc\Session\Session',
        'log' => 'hmvc\Log\Logger',
        'mailer' => 'hmvc\Mail\Mailer',
        'db' => 'hmvc\Database\ConnectionFactory'
    );

    const INJECT_TYPE = 0x01;

    private function __construct() {
        
    }

    public static function has($name) {
        return isset(static::$definitions[$name]);
    }

    public static function getClass($name) {
        if (isset(static::$definitions[$name])) {
            return static::$definitions[$name];
        }
        return false;
    }

    public static function set($name, $className) {
        if (isset(static::$definitions[$name])) {
            throw new \Exception("$name 已经存在");
        } else if (!class_exists($className)) {
            throw new \Exception("$className 不存在");
        }

        static::$definitions[$name] = $className;
    }

    public static function register($classmaps) {
        if (is_array($classmaps)) {
            foreach ($classmaps as $name => $className) {
                static::set($name, $className);
            }
        }
    }

}
