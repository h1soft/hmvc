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

namespace hmvc\Container;

use hmvc\Core\Definition;

/**
 * Description of Singleton
 *
 * @author Administrator
 */
abstract class Container implements ContainerInterface {

    protected static $instance;
    protected $objects = array();

    public function get($name) {
        if ($this->has($name)) {
            return $this->objects[$name];
        }
        //check definition
        if (Definition::has($name)) {
            $className = Definition::getClass($name);
            $classReflect = new \ReflectionClass($className);
            $params = array();
            if ($classReflect->implementsInterface('hmvc\Constraints\DefinitionInterface')) {
                $object = call_user_func_array(array(new $className(), 'factory'), $params);
            } else {
                $method = $classReflect->getConstructor();
                foreach ($method->getParameters() as $arg) {
                    $params[$arg->name] = $this->get($arg->name);
                }
                $object = $classReflect->newInstanceArgs($params);
            }
            $this->singleton($name, $object);
            return $object;
        }
        return NULL;
    }

    /**
     * 
     * @param type $name
     * @param type $class
     */
    public function singleton($name, $class, $params = array()) {
        if ($this->has($name)) {
            trigger_error("类名: " . $name . "已存在");
        }
        if (is_callable($class)) {
            $this->set($name, $class());
        } else if (is_string($class)) {
            $this->set($name, empty($params) ? new $class() : new $class($params));
        } else {
            $this->set($name, $class);
        }
    }

    public function make($name, $class, $params = array()) {
        if ($this->has($name)) {
            trigger_error("类名: " . $class . "已存在");
        } else if (!class_exists($class)) {
            trigger_error($class . " Class not found");
        }

        $instance = empty($params) ? new $class() : new $class($params);
        $this->set($name, $instance);
        return $instance;
    }

    public function set($name, $class) {
        $this->objects[$name] = $class;
    }

    public function register($name, $class, $params = array()) {
        $this->make($name, $class, $params);
    }

    public function has($name) {
        if (isset($this->objects[$name])) {
            return true;
        }
        return false;
    }

    public static function getInstance() {
        return static::$instance;
    }

    public static function setInstance($container) {
        static::$instance = $container;
    }

    public static function instance() {
        return static::$instance;
    }

    public function __get($name) {
        return $this->get($name);
    }

}
