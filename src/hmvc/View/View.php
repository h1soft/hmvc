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

namespace hmvc\View;

use ArrayAccess;
use hmvc\Core\Config;
use hmvc\Constraints\Renderable;
use hmvc\Constraints\Jsonable;

/**
 * Description of View
 *
 * @author Administrator
 */
class View implements ArrayAccess, Renderable, Jsonable {

    /**
     *
     * @var string View Name
     */
    protected $name;

    /**
     *
     * @var string templateName
     */
    protected $viewName;

    /**
     *
     * @var Engine
     */
    protected $engine;

    /**
     *
     * @var array View Data
     */
    protected $data;

    /**
     *
     * @var string View Path
     */
    protected $path;
    protected $fullPath;

    public function __construct($name = '', $data = array()) {
        $this->viewName = $name;
        $this->data = $data;
        $default = Config::get('view.default');
        if ($default) {
            $this->path = Config::get('view.path') . DIRECTORY_SEPARATOR . $default;
        } else {
            $this->path = Config::get('view.path');
        }
        $this->prepare();
    }

    private function prepare() {
        if (is_file($this->path . DIRECTORY_SEPARATOR . $this->viewName . '.php')) {
            $this->engine = new PhpEngine($this);
            $this->fullPath = $this->path . DIRECTORY_SEPARATOR . $this->viewName . '.php';
        } else if (is_file($this->path . DIRECTORY_SEPARATOR . $this->viewName . '.twig.html')) {
            $this->engine = new TwigEngine($this);
            $this->fullPath = $this->path . DIRECTORY_SEPARATOR . $this->viewName . '.twig.html';
        } else if (is_file($this->path . DIRECTORY_SEPARATOR . $this->viewName . '.smarty.html')) {
            $this->engine = new SmartyEngine($this);
            $this->fullPath = $this->path . DIRECTORY_SEPARATOR . $this->viewName . '.smarty.html';
        } else {
            throw new \hmvc\Exception\NotFoundException('Template ' . $this->viewName . ' was not found');
        }
    }

    public function getTplName($name) {
        if (is_file($this->path . DIRECTORY_SEPARATOR . $name . '.php')) {
            return $this->path . DIRECTORY_SEPARATOR . $name . '.php';
        } else if (is_file($this->path . DIRECTORY_SEPARATOR . $name . '.twig.html')) {
            return $this->path . DIRECTORY_SEPARATOR . $name . '.twig.html';
        } else if (is_file($this->path . DIRECTORY_SEPARATOR . $name . '.smarty.html')) {
            return $this->path . DIRECTORY_SEPARATOR . $name . '.smarty.html';
        }
        return NULL;
    }

    public static function make($name = '', array $data = array()) {
        return new View($name, $data);
    }

    public function render($data = array()) {
        $this->setData($data);
        return $this->engine->getRender($this->fullPath, $this->data);
    }

    public function layout($layout) {
        $fullPath = $this->getTplName($layout);
        if ($fullPath == NULL) {
            throw new \hmvc\Exception\NotFoundException('Layout ' . $layout . ' was not found');
        }
        return $this->engine->getRender($fullPath, $this->data);
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public function setData($data = array()) {
        if (!empty($data)) {
            $this->data = array($this->data, $data);
        }
        return $this;
    }

    public function offsetExists($key) {
        return array_key_exists($key, $this->data);
    }

    public function offsetGet($key) {
        return $this->data[$key];
    }

    public function offsetSet($key, $value) {
        $this->data[$key] = $value;
    }

    public function offsetUnset($key) {
        unset($this->data[$key]);
    }

    public function &__get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return app()->get($key);
    }

    public function __set($key, $value) {
        $this->data[$key] = $value;
    }

    public function __isset($key) {
        return isset($this->data[$key]);
    }

    public function __unset($key) {
        unset($this->data[$key]);
    }

    public function toJson() {
        return json_encode($this->data);
    }

}
