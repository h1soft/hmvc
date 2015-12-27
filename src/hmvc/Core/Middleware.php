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
 * Description of Middleware
 *
 * @author Administrator
 */
abstract class Middleware {
    /**
     *
     * @var \hmvc\Core\Application 
     */
    protected $app;
    
    /**
     *
     * @var \hmvc\Core\Middleware
     */
    protected $next;

    final public function setApplication(Application $app) {
        $this->app = $app;
    }

    final public function getApplication() {
        return $this->app;
    }

    final public function setNextMiddleware(Middleware $nextMiddleware) {
        $this->next = $nextMiddleware;
    }

    final public function getNextMiddleware() {
        return $this->next;
    }

    abstract public function run();
}
