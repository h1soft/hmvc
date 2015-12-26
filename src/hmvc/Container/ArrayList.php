<?php

/*
 * This file is part of the HMVC package.
 *
 * (c) Allen Niu <h@h1soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace hmvc\Container;

class ArrayList implements \ArrayAccess {

    /**
     * Data
     *
     * @var array
     * @access private
     */
    protected $element = array();

    public function __construct($elements = array()) {
        if (!empty($elements)) {
            $this->element = $elements;
        }
    }

    public function toJson() {
        return json_encode($this->element);
    }

    /**
     * 获取列表长度
     * @access public
     * @return integer
     */
    public function size() {
        return count($this->element);
    }

    /**
     * 判断元素是否为空
     * @access public
     * @return boolean
     */
    public function isEmpty() {
        return empty($this->element);
    }

    /**
     * 是否包含某个元素
     * @access public
     * @param mixed $element  查找元素
     * @return string
     */
    public function contains($element) {
        return (array_search($element, $this->element) !== false );
    }

    /**
     * 清除所有元素
     * @access public
     */
    public function clear() {
        $this->element = array();
    }

    /**
     * 增加元素
     * @access public
     * @param mixed $element  要添加的元素
     * @return boolean
     */
    public function add($element) {
        return (array_push($this->element, $element)) ? true : false;
    }

    //
    public function unshift($element) {
        return (array_unshift($this->element, $element)) ? true : false;
    }

    //
    public function pop() {
        return array_pop($this->element);
    }

    public function getIterator() {
        return new ArrayObject($this->element);
    }

    // 列表排序    
    public function ksort() {
        ksort($this->element);
    }

    // 列表排序
    public function asort() {
        asort($this->element);
    }

    // 逆向排序
    public function rsort() {
        rsort($this->element);
    }

    // 自然排序
    public function natsort() {
        natsort($this->element);
    }

    /**
     * Get a element by key
     *
     * @param string The key element to retrieve
     * @access public
     */
    public function __get($key) {
        if (!isset($this->element[$key])) {
            return NULL;
        }
        return $this->element[$key];
    }

    /**
     * Assigns a value to the specified element
     * 
     * @param string The element key to assign the value to
     * @param mixed  The value to set
     * @access public 
     */
    public function __set($key, $value) {
        $this->element[$key] = $value;
    }

    /**
     * Whether or not an element exists by key
     *
     * @param string An element key to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function __isset($key) {
        return isset($this->element[$key]);
    }

    /**
     * Unsets an element by key
     *
     * @param string The key to unset
     * @access public
     */
    public function __unset($key) {
        unset($this->element[$key]);
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param string The offset to assign the value to
     * @param mixed  The value to set
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->element[] = $value;
        } else {
            $this->element[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     *
     * @param string An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->element[$offset]);
    }

    /**
     * Unsets an offset
     *
     * @param string The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->element[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param string The offset to retrieve
     * @access public
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->element[$offset] : null;
    }

}
