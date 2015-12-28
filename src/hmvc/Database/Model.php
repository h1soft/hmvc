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

namespace hmvc\Database;

use ArrayAccess;

/**
 * Description of Model
 *
 * @author <allen@w4u.cn>
 */
abstract class Model implements ArrayAccess {

    /**
     *
     * @var \hmvc\Database\Connection
     */
    private $db;

    /**
     *
     * @var string tablename
     */
    protected $table;

    /**
     *
     * @var string primarykey
     */
    protected $primaryKey = 'id';

    /**
     *
     * @var array db field
     */
    protected $attributes = array();

    /**
     *
     * @var boolean newrecord
     */
    public $isNewRecord = false;

    /**
     * Table Attributes
     * @param array $attributes
     */
    public function __construct(array $attributes = array()) {
        $this->db = app()->get('db');
        $this->fill($attributes);
    }

    /**
     * Set New Record
     * @param boolean $flag
     * @return \hmvc\Database\Model
     */
    public function setNewRecord($flag) {
        $this->isNewRecord = $flag;
        return $this;
    }

    /**
     * Get TableName or Class Basename
     * @return string Table Name
     */
    public function getTable() {
        if (isset($this->table)) {
            return $this->table;
        }
        return class_basename($this);
    }

    /**
     * Set table name
     * @param string $table
     * @return \hmvc\Database\Model
     */
    public function setTable($table) {
        $this->table = $table;
        return $this;
    }

    /**
     * Get PrimaryKey Name
     * @return string
     */
    public function getKeyName() {
        return $this->primaryKey;
    }

    /**
     * Set PrimaryKey
     * @param string $key
     * @return \hmvc\Database\Model
     */
    public function setKeyName($key) {
        $this->primaryKey = $key;
        return $this;
    }

    /**
     * 
     * @return int PrimaryKey Value
     */
    public function getKeyValue() {
        return $this->getAttribute($this->getPrimaryKey());
    }

    public function setConnection($db) {
        $this->db = $db;
        return $this;
    }

    public function save() {
        if ($this->isNewRecord) {
            $this->db->insert($this->getTable(), $this->getAttributes());
            $this->setNewRecord(false);
            $this->setAttribute($this->getPrimaryKey(), $this->db->lastInsertId());
        } else {
            $pkey = $this->getPrimaryKey();
            $this->from($this->getTable())->set($this->getAttributes())
                    ->where($pkey . "=:" . $pkey, array(':' . $pkey => $this->getKeyValue()))
                    ->update();
        }
        return $this;
    }

    /**
     * Update Table
     * @param array $attributes
     * @return type
     */
    public function update(array $attributes = array()) {
        if ($this->isNewRecord) {
            return $this->db->insert($this->getTable(), $attributes);
        }
        $this->fill($attributes)->save();
        return $this;
    }

    /**
     * fill attributes
     * @param array $attributes
     * @return \hmvc\Database\Model
     */
    public function fill(array $attributes = array()) {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    public function destory() {
        $id = $this->getKeyValue();
        if (!$id) {
            return false;
        }
        $pkey = $this->getPrimaryKey();
        return $this->db->delete($this->getTable(), $pkey . '=:' . $pkey, array(
                    $pkey => $id
        ));
    }

    /**
     * 
     * @param array|int $ids
     * @return \hmvc\Database\Model|array
     */
    public static function find($ids) {
        $ids = is_array($ids) ? $ids : func_get_args();
        $model = new static;
        $model->setConnection(Connection::getConnection());
        $model->db->prepare("select * from " . $model->db->tableName($model->getTable()) . " where " . $model->getPrimaryKey() . ' IN (:id)', array(
            ':id' => implode(',', $ids)
        ));
        $model->db->execute();
        if (func_num_args() == 1) {
            return $model->db->fetchObject(get_called_class());
        }
        return $model->db->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }

    /**
     * 
     * @param type $ids
     * @return type
     */
    public static function delete($ids) {
        $ids = is_array($ids) ? $ids : func_get_args();
        $model = new static;
        return $model->from($model->getTable())->whereIn($model->getPrimaryKey(), $ids)->delete();
    }

    /**
     * 
     * @param array $attributes
     * @return \static
     */
    public static function create(array $attributes = array()) {
        $model = new static($attributes);
        $model->setNewRecord(true);
        $model->save();
        return $model;
    }

    /**
     * 
     * @param type $connection
     * @return type
     */
    public static function using($connection = 'default') {
        $model = new static;
        $model->setConnection(Connection::getConnection($connection));
        return $model;
    }

    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function hasAttribute($key) {
        return isset($this->attributes[$key]);
    }

    public function getAttribute($key) {
        if ($this->hasAttribute($key)) {
            return $this->attributes[$key];
        }
        return NULL;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function getPrimaryKey() {
        return $this->primaryKey;
    }

    public function toJson() {
        return json_encode($this->attributes);
    }

    public function toXML() {
        $class = strtolower(class_basename(get_called_class()));
        return xmlEncode(array($class => $this->attributes));
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->$offset);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key) {
        unset($this->attributes[$key]);
    }

    /**
     * 
     * @param type $method
     * @param type $parameters
     * @return \hmvc\Database\Query
     */
    public function __call($method, $parameters) {
        $query = DB::createQuery();
        return call_user_func_array(array($query, $method), $parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters) {
        $instance = new static;
        return call_user_func_array(array($instance, $method), $parameters);
    }

}
