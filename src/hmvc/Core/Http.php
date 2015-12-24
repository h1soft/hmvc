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

use hmvc\Http\Request;
use hmvc\Routing\Router;
use hmvc\Events\Event;

/**
 * Description of Http
 *
 * @author Administrator
 */
class Http implements KernelInterface {

    protected $app;
    protected $router;
    protected $request;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->request = Request::classic();
        $this->router = new Router($app);

        $this->app->set('request', $this->request);
        $this->app->set('router', $this->router);
        Event::send('system.init');
    }

    public function dispatch() {
        Event::send('system.router');
        if ($this->router->isHMVC() && $this->router->hmvcDispatch($this->request->getPathInfo())) {
            return false;
        }
        $matchedRoutes = $this->router->getMatchedRoutes($this->request->getMethod(), $this->request->getPathInfo());
        $dispatched = null;
        foreach ($matchedRoutes as $route) {
            try {
                $dispatched = $route->dispatch();
                if ($dispatched) {
                    break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        if (!$dispatched) {
//            throw new \Exception('not found');
            trigger_error('not found');
//            echo 'not found';
        }
        Event::send('system.routed');
    }

    public function getName() {
        return 'hmvc\Core\Http';
    }

}
