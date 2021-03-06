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

use ReflectionMethod;
use Exception;
use hmvc\Events\Event;
use hmvc\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Description of HmvcDispatcher
 *
 * @author Administrator
 */
class MvcDispatcher {

    protected $namespace;

    /**
     *
     * @var \hmvc\Core\Application
     */
    protected $app;

    /**
     *
     * @var \hmvc\Http\Request
     */
    protected $request;

    /**
     *
     * @var string
     */
    public $prefix;
    public $controllerName;
    public $actionName;
    protected $originActionName;
    protected $caseSensitive = false;
    protected $hmvcParams;
    protected $params = array();
    protected $paramNames = array();
    protected $paramNamesPath = array();
    protected $conditions = array(
        'id' => '(\d+)',
    );
    protected $resourceDefaults = array('index', 'create', 'save', 'show', 'edit', 'update', 'destroy');
    protected $isPathParam = false;

    public function __construct($prefix, $params, $app) {
        $this->namespace = $params['namespace'];
        $this->app = $app;
        $this->prefix = rtrim($prefix, '/');
        $this->hmvcParams = $params;
        $this->request = $app->request;
    }

    public function dispatch() {
        $this->parseRequest();
        $response = $this->callAction();
        if ($response instanceof SymfonyResponse) {
            $response->prepare($this->request);
        } else {
            $response = Response::create($response, 200)->prepare($this->request);
        }
//        else {
//            $response = Response::create($content, 200)->prepare($this->request);
//        }
        return $response;
    }

    private function parseRequest() {
        $pathinfo_uri = preg_replace("#{$this->prefix}#u", '', $this->request->getPathInfo(), 1);
        $router_segment = explode('/', trim($pathinfo_uri, '/'));
        $this->controllerName = array_shift($router_segment);
        $this->actionName = array_shift($router_segment);
        $this->isPathParam = array_shift($router_segment);
        $this->originActionName = $this->actionName;        
        $this->controllerName = empty($this->controllerName) ? ucfirst($this->hmvcParams['controller']) : ucfirst($this->controllerName);        
        $this->actionName = empty($this->actionName) ? $this->hmvcParams['action'] : $this->actionName;
        $className = "{$this->namespace}\\Controller\\{$this->controllerName}";
        if (class_exists($className)) {
            $controller = new $className();
            if ($controller instanceof Controller) {
                $controller->init();
                $this->app->set('controller', $controller);
            } else {
                throw new Exception("not found");
            }
        } else {
            throw new Exception("not found");
        }
    }

    private function callAction() {
        $controller = $this->app->get('controller'); //当前Controller

        if ($controller->__isRESTfull()) {
            $this->actionName = $this->getDefaultMethod();
            $controllerMethod = new ReflectionMethod($controller, $this->actionName);
        } else if (method_exists($controller, $this->actionName)) {
            $controllerMethod = new ReflectionMethod($controller, $this->actionName);
        } else if (method_exists($controller, $this->actionName . 'Action')) {
            $controllerMethod = new ReflectionMethod($controller, $this->actionName . 'Action');
        }
        if (!isset($controllerMethod)) {
            $controller->methodNotFound("{$this->namespace}\\Controller\\{$this->controllerName}#{$this->actionName} method does not exist.");
        }
        $route = $controller->getRoute($this->actionName);
        $this->matches('/' . $route);
        $this->request->_setParams($this->params);
        $controller->beforeAction();
        $response = false;
        if ($controllerMethod->getNumberOfParameters() > 0) {
            $parameters = array();
            foreach ($controllerMethod->getParameters() as $param) {
                $parameters[$param->getName()] = array_get($this->params, $param->getName(), $param->isDefaultValueAvailable() ? $param->getDefaultValue() : $this->app->get($param->getName()) );
            }
            $response = $controllerMethod->invokeArgs($controller, $parameters);
        } else {
            $response = $controllerMethod->invoke($controller);
        }
        $controller->afterAction();
        Event::send('system.routed');
        return $response;
    }

    private function getDefaultMethod() {
        $controller = $this->app->get('controller');
        $method = $this->request->getMethod();
        //&& !$this->isPathParam
        if (empty($this->originActionName) && $method == 'GET') {
            return 'index';
        } else if (method_exists($controller, $this->originActionName)) {
            return $this->originActionName;
        } else if (method_exists($controller, $method . $this->originActionName)) {
            return strtolower($method) . ucfirst($this->originActionName);
        }
        switch ($method) {
            case 'PUT':
                return 'update';
            case 'POST':
                return 'save';
            case 'DELETE':
                return 'destroy';
            case 'GET':
                return 'show';
        }
    }

    public function matches($resourceUri) {
        if (!$resourceUri) {
            return false;
        }
        $patternAsRegex = preg_replace_callback(
                '#:([\w]+)\+?#', array($this, 'matchesCallback'), str_replace(')', ')?', (string) $resourceUri)
        );
        if (substr($resourceUri, -1) === '/') {
            $patternAsRegex .= '?';
        }
        $regex = '#' . $patternAsRegex . '$#';

        if ($this->caseSensitive === false) {
            $regex .= 'i';
        }
        $paramValues = '';
        if (!preg_match($regex, $this->request->getPathInfo(), $paramValues)) {
            return false;
        }
        foreach ($this->paramNames as $name) {
            if (isset($paramValues[$name])) {
                if (isset($this->paramNamesPath[$name])) {
                    $this->params[$name] = explode('/', urldecode($paramValues[$name]));
                } else {
                    $this->params[$name] = urldecode($paramValues[$name]);
                }
            }
        }
        return true;
    }

    protected function matchesCallback($m) {
        $this->paramNames[] = $m[1];
        if (isset($this->conditions[$m[1]])) {
            return '(?P<' . $m[1] . '>' . $this->conditions[$m[1]] . ')';
        }
        if (substr($m[0], -1) === '+') {
            $this->paramNamesPath[$m[1]] = 1;
            return '(?P<' . $m[1] . '>.+)';
        }

        return '(?P<' . $m[1] . '>[^/]+)';
    }

    public function setConditions($name, $value = null) {
        if (is_array($name)) {
            $this->conditions = array_merge($this->conditions, $value);
        } else if (is_string($name) && is_string($value)) {
            $this->conditions[$name] = $value;
        }
    }

    public function getPathController() {
        return $this->prefix . '/' . strtolower($this->moduleName . '/' . $this->controllerName);
    }

    public function getPathAction() {
        return $this->prefix . '/' . strtolower($this->moduleName . '/' . $this->controllerName . '/' . $this->actionName);
    }

    public function getPathModule() {
        return $this->prefix . '/' . strtolower($this->moduleName);
    }

    public function getPathPrefix() {
        return $this->prefix;
    }

}
