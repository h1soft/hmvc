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

namespace hmvc\Events;

use hmvc\Core\Application;

/**
 * Description of Dispatcher
 *
 * @author Administrator
 */
final class Dispatcher {

    private $app;
    private $listeners = array();

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function listen($eventName, $callable) {
        if (is_string($callable) && class_exists($callable)) {
            $callable = function() use ($callable) {
                static $obj = null;
                if ($obj === null) {
                    $obj = new $callable;
                }
                return call_user_func_array(array($obj, 'event'), func_get_args());
            };
        }
        if (isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = array();
            $this->listeners[$eventName][] = $callable;
        } else {
            $this->listeners[$eventName][] = $callable;
        }
    }

    public function register($eventName, $callable) {
        $this->listen($eventName, $callable);
    }

    public function hasListen($eventName) {
        return isset($this->listeners[$eventName]);
    }

    public function trigger($eventName) {
        $events = isset($this->listeners[$eventName]) ? $this->listeners[$eventName] : array();
        foreach ($events as $callable) {
            call_user_func_array($callable, array($eventName));
        }
    }

    public function fire($eventName) {
        return $this->trigger($eventName);
    }

    public function flush() {
        $this->listeners = array();
    }

}
