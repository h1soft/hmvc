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

use hmvc\Core\Application;

/**
 * 
 * @return \hmvc\Core\Application
 */
function app() {
    return Application::getInstance();
}

/**
 * 
 * @param string $path
 * @return baseurl
 */
function baseUrl($path = NULL) {
    if ($path) {
        return app()->get('request')->baseUrl() . $path;
    }
    return app()->get('request')->baseUrl();
}

/**
 * BasePath
 * @param type $path
 * @return string basePath
 */
function base_path($path = '') {
    return app()->basePath() . DIRECTORY_SEPARATOR . $path;
}

function storage_path() {
    return app()->basePath() . DIRECTORY_SEPARATOR . app()->storagePath();
}

function config_path() {
    return app()->basePath() . DIRECTORY_SEPARATOR . app()->configPath();
}

function assets_path() {
    return app()->basePath() . DIRECTORY_SEPARATOR . app()->assetsPath();
}

function resources_path() {
    return app()->basePath() . DIRECTORY_SEPARATOR . app()->resourcesPath();
}

function e($html) {
    return htmlentities($html, ENT_QUOTES, 'UTF-8', false);
}

function object_get($object, $key, $default = null) {
    if (is_null($key) || trim($key) == '') {
        return $object;
    }
    foreach (explode('.', $key) as $segment) {
        if (!is_object($object) || !isset($object->{$segment})) {
            return value($default);
        }
        $object = $object->{$segment};
    }
    return $object;
}

function array_get($array, $key, $default = null) {
    if (is_null($key) || trim($key) == '') {
        return $array;
    }
    return isset($array[$key]) ? $array[$key] : $default;
}

function value($value) {
    return $value;
}

function xmlEncode($mixed, $domElement = null, $DOMDocument = null) {
    if (is_null($DOMDocument)) {
        $DOMDocument = new DOMDocument;
        $DOMDocument->formatOutput = true;
        xmlEncode($mixed, $DOMDocument, $DOMDocument);
        echo $DOMDocument->saveXML();
    } else {
        if (is_array($mixed)) {
            foreach ($mixed as $index => $mixedElement) {
                if (is_int($index)) {
                    if ($index === 0) {
                        $node = $domElement;
                    } else {
                        $node = $DOMDocument->createElement($domElement->tagName);
                        $domElement->parentNode->appendChild($node);
                    }
                } else {
                    $plural = $DOMDocument->createElement($index);
                    $domElement->appendChild($plural);
                    $node = $plural;
                    if (!(rtrim($index, 's') === $index)) {
                        $singular = $DOMDocument->createElement(rtrim($index, 's'));
                        $plural->appendChild($singular);
                        $node = $singular;
                    }
                }

                xmlEncode($mixedElement, $node, $DOMDocument);
            }
        } else {
            $domElement->appendChild($DOMDocument->createTextNode($mixed));
        }
    }
}

/**
 * 获取变量
 * @param type $value
 * @param type $default
 * @return type
 */
function get_default($value, $default = NULL) {
    if (isset($value)) {
        return $value;
    }
    return $default;
}

function hmvcError($code, $message, $file, $line) {
    if (0 == error_reporting()) {
        return;
    }
    if ($code) {
        hmvc\Core\StackTrace::systemError($message, true, false);
    }
}

function hmvcExceptionHandler($exception) {
    if (0 == error_reporting()) {
        return;
    }
    hmvc\Core\StackTrace::exceptionError($exception);
}

/**
 * array to object
 * @param type $array
 * @return boolean|\stdClass
 */
function arrayToObject($array) {
    if (!is_array($array)) {
        return $array;
    }

    $object = new stdClass();
    if (is_array($array) && count($array) > 0) {
        foreach ($array as $name => $value) {
            $name = strtolower(trim($name));
            if (!empty($name)) {
                $object->$name = arrayToObject($value);
            }
        }
        return $object;
    } else {
        return FALSE;
    }
}

/**
 * get classname
 * @param type $classname
 * @return string
 */
function class_basename($classname) {
    $classname = is_object($classname) ? get_class($classname) : $classname;
    return basename(str_replace('\\', '/', $classname));
}

/**
 * dump objects
 */
function pp() {
    $params = func_get_args();
    foreach ($params as $value) {
        print_r($value);
    }
}
