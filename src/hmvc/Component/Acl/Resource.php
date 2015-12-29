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

namespace hmvc\Component\Acl;

use Exception;

/**
 * Package hmvc\Component\Acl  
 * 
 * Class Resource
 *
 * @author allen <allen@w4u.cn>
 */
abstract class Resource {

    /**
     *
     * @var string prefix
     */
    public $namespace;

    /**
     *
     * @var string modulename
     */
    public $module;

    /**
     *
     * @var controller and actions
     */
    protected static $resources = array();
    private $_prevAddKey;

    public function __construct($namespace = '') {
        $this->namespace = $namespace;
        $this->initialize();
    }

    protected function initialize() {
        
    }

    public function addResource($name, $text, $default = NULL) {
        if (!$default) {
            $default = array(
                'view' => '查看',
                'create' => '添加',
                'edit' => '修改',
                'delete' => '删除',
            );
        }
        static::$resources[$this->module][$name] = array(
            'name' => $text,
            'actions' => $default
        );
        $this->_prevAddKey = $name;
    }

    public function addController($name, $text, $actions = array()) {
        static::$resources[$this->module][$name] = array(
            'name' => $text,
            'actions' => array()
        );
        if (is_array($actions)) {
            static::$resources[$this->module][$name]['actions'] = $actions;
        }
        $this->_prevAddKey = $name;
        return $this;
    }

    public function addAction($name, $text) {
        static::$resources[$this->module][$this->_prevAddKey]['actions'][$name] = $text;
        return $this;
    }

    public static function resources() {
        return static::$resources;
    }

    public static function getResources() {
        return static::$resources;
    }

}
