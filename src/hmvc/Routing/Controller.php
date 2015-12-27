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

use \Exception;
use hmvc\Core\Application;

/**
 * Description of Controller
 *
 * @author Administrator
 */
abstract class Controller {

    protected $routes = array(
        'index' => ':id',
        'show' => ':id',
        'edit' => ':id',
        'save' => ':id',
        'update' => ':id',
        'destroy' => ':id',
    );

    /**
     *
     * @var \hmvc\Core\Application 
     */
    protected $app;

    /**
     *
     * @var \hmvc\Http\Request $request
     */
    protected $request;

    /**
     * default RESTfull
     * @var bool
     */
    protected $RESTfull = true;

    public function __construct() {
        $this->app = Application::instance();
        $this->request = $this->app->get('request');
    }

    public function getRoutes() {
        return $this->routes;
    }

    public function getRoute($actionName) {
        return isset($this->routes[$actionName]) ? $this->routes[$actionName] : '(/:one(/:two(/:three)))';
    }

    /**
     * 
     * @return boolean
     */
    public function __isRESTfull() {
        return $this->RESTfull;
    }

    public function methodNotFound($message) {
        throw new Exception($message);
    }

    public function param($name) {
        return $this->request->param($name);
    }

    public function setParamCondition($name, $condition) {
        return $this->app->hmvc->setConditions($name, $condition);
    }

    public function setConditions($conditions) {
        return $this->app->hmvc->setConditions($conditions);
    }

    public function __get($name) {
        return $this->app->get($name);
    }

    public function get($name) {
        return $this->app->get($name);
    }

    /**
     * 
     * @param string $name
     * @return \hmvc\Database\Connection
     */
    public function db($name = null) {
        $db = $this->app->get('db');
        if (!empty($name)) {
            return $db->using($name);
        }
        return $db;
    }

    public function init() {
        
    }

    public function beforeAction() {
        
    }

    public function afterAction() {
        
    }

}
