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
    protected $_hdata = array();

    /**
     * Get a _hdata by key
     *
     * @param string The key _hdata to retrieve
     * @access public
     */
    public function __get($key) {
        if (!isset($this->_hdata[$key])) {
            return NULL;
        }
        return $this->_hdata[$key];
    }

    /**
     * Assigns a value to the specified _hdata
     * 
     * @param string The _hdata key to assign the value to
     * @param mixed  The value to set
     * @access public 
     */
    public function __set($key, $value) {
        $this->_hdata[$key] = $value;
    }

    /**
     * Whether or not an _hdata exists by key
     *
     * @param string An _hdata key to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function __isset($key) {
        return isset($this->_hdata[$key]);
    }

    /**
     * Unsets an _hdata by key
     *
     * @param string The key to unset
     * @access public
     */
    public function __unset($key) {
        unset($this->_hdata[$key]);
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
            $this->_hdata[] = $value;
        } else {
            $this->_hdata[$offset] = $value;
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
        return isset($this->_hdata[$offset]);
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
            unset($this->_hdata[$offset]);
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
        return $this->offsetExists($offset) ? $this->_hdata[$offset] : null;
    }

}
