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

namespace hmvc\Routing;

use hmvc\Core\Application;
use hmvc\Http\Request;

/**
 * Description of Router
 *
 * @author Administrator
 */
class Router {

    const HMVC_HANDLE = 'hmvc\Routing\HmvcDispatcher';
    const MVC_HANDLE = 'hmvc\Routing\MvcDispatcher';

    /**
     * @var Route The current route (most recently dispatched)
     */
    protected $currentRoute;

    /**
     * Module/Controler/Action
     * @var type 
     */
    protected $hmvcs = array();

    /**
     * HMVC 
     * @var bool
     */
    protected $isHMVC = false;

    /**
     * @var array Lookup hash of all route objects
     */
    protected $routes;

    /**
     * @var array Lookup hash of named route objects, keyed by route name (lazy-loaded)
     */
    protected $namedRoutes;

    /**
     * @var array Array of route objects that match the request URI (lazy-loaded)
     */
    protected $matchedRoutes;

    /**
     * @var array Array containing all route groups
     */
    protected $routeGroups;

    /**
     *
     * @var \hmvc\Core\Application 
     */
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->routes = array();
        $this->routeGroups = array();
    }

    public function isHMVC() {
        return $this->isHMVC;
    }

    protected function _addRoute($args) {
        $pattern = array_shift($args);
        $callable = array_pop($args);
        $route = new Route($pattern, $callable, true);
        $this->map($route);

        return $route;
    }

    /**
     * 
     * @return \hmvc\Routing\Route
     */
    public function AddRoute() {
        $args = func_get_args();
        return $this->_addRoute($args);
    }

    public function hmvc($prefix, $params) {
        $this->isHMVC = true;
        if (is_string($params)) {
            $this->hmvcs[$prefix] = array(
                'namespace' => $params,
                'handle' => Router::HMVC_HANDLE,
                'module' => 'default',
                'controller' => 'index',
                'action' => 'index',
            );
        } else if (is_array($params)) {
            $this->hmvcs[$prefix] = array(
                'namespace' => $params['namespace'],
                'handle' => Router::HMVC_HANDLE,
                'module' => isset($params['module']) ? $params['module'] : 'default',
                'controller' => isset($params['controller']) ? $params['controller'] : 'index',
                'action' => isset($params['action']) ? $params['action'] : 'index',
            );
        }
    }

    /**
     * 
     * @return \hmvc\Routing\Route
     */
    public function get() {
        $args = func_get_args();
        return $this->_addRoute($args)->via(Request::METHOD_GET);
    }

    /**
     * 
     * @return \hmvc\Routing\Route
     */
    public function post() {
        $args = func_get_args();
        return $this->_addRoute($args)->via(Request::METHOD_POST);
    }

    /**
     * 
     * @return \hmvc\Routing\Route
     */
    public function delete() {
        $args = func_get_args();
        return $this->_addRoute($args)->via(Request::METHOD_DELETE);
    }

    /**
     * 
     * @return \hmvc\Routing\Route
     */
    public function put() {
        $args = func_get_args();
        return $this->_addRoute($args)->via(Request::METHOD_PUT);
    }

    /**
     * 
     * @return \hmvc\Routing\Route
     */
    public function any() {
        $args = func_get_args();
        return $this->_addRoute($args)->via(Request::METHOD_ANY);
    }

    public function group() {
        $args = func_get_args();
        $pattern = array_shift($args);
        $callable = array_pop($args);
        $this->pushGroup($pattern, $args);
        if (is_callable($callable)) {
            call_user_func($callable);
        }
        $this->popGroup();
    }

    public function getCurrentRoute() {
        if ($this->currentRoute !== null) {
            return $this->currentRoute;
        }
        if (is_array($this->matchedRoutes) && count($this->matchedRoutes) > 0) {
            return $this->matchedRoutes[0];
        }
        return null;
    }

    /**
     * 
     * @param string $resourceUri
     * @return boolean|\hmvc\Routing\HmvcDispatcher
     */
    public function hmvcDispatch($resourceUri) {
        foreach ($this->hmvcs as $prefix => $params) {
            if (\hmvc\Helpers\Str::startsWith($resourceUri, $prefix, false)) {
                $handleClass = $params['handle'];
                $hmvc = new $handleClass($prefix, $params, $this->app);
                $this->app->set('hmvc', $hmvc);
                return $hmvc->dispatch();
            }
        }
        return false;
    }

    public function getMatchedRoutes($httpMethod, $resourceUri, $reload = false) {
        if ($reload || is_null($this->matchedRoutes)) {
            $this->matchedRoutes = array();
            foreach ($this->routes as $route) {
                if (!$route->supportsHttpMethod($httpMethod) && !$route->supportsHttpMethod("ANY")) {
                    continue;
                }
                if ($route->matches($resourceUri)) {
                    $this->matchedRoutes[] = $route;
                }
            }
        }
        return $this->matchedRoutes;
    }

    public function map(\hmvc\Routing\Route $route) {
        list($groupPattern, $groupMiddleware) = $this->processGroups();
        $route->setPattern($groupPattern . $route->getPattern());
        $this->routes[] = $route;
        foreach ($groupMiddleware as $middleware) {
            $route->setMiddleware($middleware);
        }
    }

    protected function processGroups() {
        $pattern = "";
        $middleware = array();
        foreach ($this->routeGroups as $group) {
            $k = key($group);
            $pattern .= $k;
            if (is_array($group[$k])) {
                $middleware = array_merge($middleware, $group[$k]);
            }
        }
        return array($pattern, $middleware);
    }

    public function pushGroup($group, $middleware = array()) {
        return array_push($this->routeGroups, array($group => $middleware));
    }

    public function popGroup() {
        return (array_pop($this->routeGroups) !== null);
    }

    public function urlFor($name, $params = array()) {
        if (!$this->hasNamedRoute($name)) {
            throw new \RuntimeException('Named route not found for name: ' . $name);
        }
        $search = array();
        foreach ($params as $key => $value) {
            $search[] = '#:' . preg_quote($key, '#') . '\+?(?!\w)#';
        }
        $pattern = preg_replace($search, $params, $this->getNamedRoute($name)->getPattern());
        //Remove remnants of unpopulated, trailing optional pattern segments, escaped special characters
        return preg_replace('#\(/?:.+\)|\(|\)|\\\\#', '', $pattern);
    }

    public function addNamedRoute($name, $route) {
        if ($this->hasNamedRoute($name)) {
            throw new \RuntimeException('Named route already exists with name: ' . $name);
        }
        $this->namedRoutes[(string) $name] = $route;
    }

    public function hasNamedRoute($name) {
        $this->getNamedRoutes();
        return isset($this->namedRoutes[(string) $name]);
    }

    public function getNamedRoute($name) {
        $this->getNamedRoutes();
        if ($this->hasNamedRoute($name)) {
            return $this->namedRoutes[(string) $name];
        }
        return null;
    }

    public function getNamedRoutes() {
        if (is_null($this->namedRoutes)) {
            $this->namedRoutes = array();
            foreach ($this->routes as $route) {
                if ($route->getName() !== null) {
                    $this->addNamedRoute($route->getName(), $route);
                }
            }
        }
        return new \ArrayIterator($this->namedRoutes);
    }

}
