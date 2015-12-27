<<<<<<< HEAD
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

/**
 * Description of View
 *
 * @author Administrator
 */
class View implements ArrayAccess {

    protected $engine;
    protected $data;
    protected $path;

    public static function make($templateName = '') {
        
    }

    public function render() {
        
    }

    public function renderString() {
        
    }

    public function renderJSON() {
        
    }

    public function renderXML() {
        
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function offsetExists($key) {
        return array_key_exists($key, $this->data);
    }

    public function offsetGet($key) {
        return $this->data[$key];
    }

    public function offsetSet($key, $value) {
        $this->with($key, $value);
    }

    public function offsetUnset($key) {
        unset($this->data[$key]);
    }

    public function &__get($key) {
        return $this->data[$key];
    }

    public function __set($key, $value) {
        $this->with($key, $value);
    }

    public function __isset($key) {
        return isset($this->data[$key]);
    }

    public function __unset($key) {
        unset($this->data[$key]);
    }

}
=======
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

/**
 * Description of View
 *
 * @author Administrator
 */
class View implements ArrayAccess {

    protected $engine;
    protected $data;
    protected $path;

    public static function make($templateName = '') {
        echo \hmvc\Core\Config::get('view.path');
    }

    public function render() {
        
    }

    public static function renderString($data) {
        $response = new \hmvc\Http\Response($data, 200);
//        $response->send();
        return $response;
    }

    public static function renderJSON($data) {
        $response = new \hmvc\Http\Response(json_encode($data));
        $response->send();
    }

    public function renderXML() {
        
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function offsetExists($key) {
        return array_key_exists($key, $this->data);
    }

    public function offsetGet($key) {
        return $this->data[$key];
    }

    public function offsetSet($key, $value) {
        $this->with($key, $value);
    }

    public function offsetUnset($key) {
        unset($this->data[$key]);
    }

    public function &__get($key) {
        return $this->data[$key];
    }

    public function __set($key, $value) {
        $this->with($key, $value);
    }

    public function __isset($key) {
        return isset($this->data[$key]);
    }

    public function __unset($key) {
        unset($this->data[$key]);
    }

}
>>>>>>> origin/master
