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
 * Description of Event
 *
 * @author Administrator
 */
final class Event {
    
    const SYSTEM_INI = 0;
    const SYSTEM_ROUTER = 0;

    public static function send($eventName) {
        Application::getInstance()->events->fire($eventName);
    }

    public static function trigger($eventName) {
        Application::getInstance()->events->fire($eventName);
    }

    public static function push($eventName, $handleClass) {
        Application::getInstance()->events->register($eventName, $handleClass);
    }

}
