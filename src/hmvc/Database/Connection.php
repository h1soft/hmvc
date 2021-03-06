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

use PDO;
use hmvc\Core\Config;
use hmvc\Helpers\Arr;

/**
 * Database Connection
 * 
 * Support (MySQL \ PGSQL \ SQLite)
 *
 * @author <allen@w4u.cn>
 */
class Connection {

    /**
     *
     * @var array params
     */
    protected $params;

    /**
     *
     * @var array PDO attrs
     */
    protected $attributes = array();

    /**
     *
     * @var array connections
     */
    protected static $connections = array();

    /**
     *
     * @var string default connection
     */
    protected $connectionName;

    /**
     *
     * @var string table prefix
     */
    protected $tablePrefix;

    /**
     *
     * @var \hmvc\Database\Driver|\PDO
     */
    protected $driver;

    /**
     *
     * @var \PDOStatement
     */
    public $statement;

    /**
     *
     * @var int rowcount
     */
    protected $rowcount;

    /**
     *
     * @var boolean Auto Connection
     */
    protected $autoConnect = false;

    /**
     *
     * @var boolean is Connection
     */
    protected $isConnected = false;

    /**
     *
     * @var array sql history
     */
    protected $sqlCommandHistory = array();

    /**
     *
     * @var array PDO Params
     */
    protected $queryParams = array();

    public function __construct($params, $connectionName = 'default') {
        $this->connectionName = $connectionName;
        $this->params = $params;
        $this->tablePrefix = array_get($params, 'prefix', '');
        if ($this->autoConnect) {
            $this->connect();
        }
    }

    public function isAutoConnect() {
        return $this->autoConnect;
    }

    public function connect() {
        if ($this->isConnected) {
            return true;
        }
        $driver = array_get($this->params, 'driver', '');
        switch ($driver) {
            case 'mysql':
                $this->driver = new Driver\Mysql($this->params);
                break;
            case 'pgsql':
                $this->driver = new Driver\Pgsql($this->params);
                break;
            case 'sqlite':
                $this->driver = new Driver\Sqlite($this->params);
                break;
            default:
                throw new \Exception("{$driver} driver doesn't support");
        }
        static::$connections[$this->connectionName] = $this;
        $this->isConnected = true;
    }

    /**
     * 
     * @param string $connectionName
     * @return \hmvc\Database\Connection
     * @throws \Exception
     */
    public function using($connectionName) {
        if (array_key_exists($connectionName, static::$connections)) {
            return static::$connections[$connectionName];
        }
        $params = Config::get('database.connections.' . $connectionName);
        if (is_null($params) || !is_array($params)) {
            throw new \Exception('config/database.php No configuration database');
        }
        static::$connections[$connectionName] = new Connection($params, $connectionName);
        return static::$connections[$connectionName];
    }

    public function on($connectionName) {
        return $this->using($connectionName);
    }

    public function getDriver() {
        return $this->driver;
    }

    public function getDriverName() {
        return $this->driver->getName();
    }

    public function getParams() {
        return $this->params;
    }

    public function tablePrefix() {
        return array_get($this->params, 'prefix', '');
    }

    public function getAutoCommit() {
        return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
    }

    public function setAutoCommit($value) {
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT, $value);
    }

    public function setAttribute($name, $value) {
        $this->connect();
        if ($this->driver instanceof PDO) {
            $this->driver->setAttribute($name, $value);
        } else {
            $this->attributes[$name] = $value;
        }
    }

    public function getAttribute($name) {
        return $this->driver->getAttribute($name);
    }

    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * 
     * @param string $query
     * @param array $data
     * @return \PDOStatement
     */
    public function query($query, $data = null) {
        $this->connect();
        $this->statement = $this->driver->prepare($this->prepareSQL($query));
        $this->bindValues($data);
        $this->statement->execute();
        $this->rowcount = $this->statement->rowCount();
        $this->sqlCommandHistory[] = $this->statement->queryString;
        $this->statement->closeCursor();
        return $this->statement;
    }

    /**
     * 
     * @param string $statement
     * @param array $data
     * @return int rowcount
     */
    public function exec($statement, $data = null) {
        $this->connect();
        $this->statement = $this->driver->prepare($this->prepareSQL($statement));
        $this->bindValues($data);
        $this->statement->execute();
        $this->rowcount = $this->statement->rowCount();
        $this->sqlCommandHistory[] = $this->statement->queryString;
        $this->statement->closeCursor();
        return $this->rowcount;
    }

    /**
     * execute
     * results
     * @param type $fetch_style
     * @return array
     */
    public function results($fetch_style = PDO::FETCH_ASSOC) {
        $this->execute();
        $result = $this->statement->fetchAll($fetch_style);
        $this->rowcount = $this->statement->rowCount();
        $this->statement->closeCursor();
        return $result;
    }

    public function fetch($fetch_style = PDO::FETCH_ASSOC, $cursor_orientation = 'PDO::FETCH_ORI_NEXT', $cursor_offset = 0) {
        return $this->statement->fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    public function fetchObject($class_name = "stdClass", $ctor_args = array()) {
        return $this->statement->fetchObject($class_name, $ctor_args);
    }

    public function fetchAll($fetch_style = PDO::FETCH_ASSOC, $class = NULL) {
        $results = $this->statement->fetchAll($fetch_style, $class);
        $this->rowcount = $this->statement->rowCount();
        $this->statement->closeCursor();
        return $results;
    }

    public function row($fetch_style = PDO::FETCH_ASSOC) {
        return $this->statement->fetch($fetch_style);
    }

    public function first($fetch_style = PDO::FETCH_ASSOC) {
        $row = $this->statement->fetch($fetch_style);
        $this->rowcount = $this->statement->rowCount();
        $this->statement->closeCursor();
        return $row;
    }

    public function next() {
        return $this->statement->nextRowset();
    }

    /**
     * rowcount
     * @return int
     */
    public function rowCount() {
        return $this->rowcount;
    }

    /**
     * insert id
     * @param sequence $name
     * @return insert ID
     */
    public function lastInsertId($name = null) {
        $this->connect();
        return $this->driver->lastInsertId($name);
    }

    /**
     * 
     * @param string $statement
     * @param array $params
     * @param array $driver_options
     * @return \PDOStatement
     */
    public function prepare($statement, $params = array(), array $driver_options = array()) {
        $this->connect();
        $this->statement = $this->driver->prepare($this->prepareSQL($statement), $driver_options);
        $this->bindValues($params);
        return $this;
    }

    /**
     * 
     * @param string $statement
     * @param array $driver_options
     * @return \PDOStatement
     */
    public function createStatement($statement, array $driver_options = array()) {
        $this->connect();
        return $this->driver->prepare($this->prepareSQL($statement), $driver_options);
    }

    /**
     * 
     * @param string $statement
     * @param array $params
     * @return \PDOStatement
     */
    public function createQuery($statement, $params = array(), array $driver_options = array()) {
        $this->connect();
        $this->statement = $this->driver->prepare($this->prepareSQL($statement), $driver_options);
        $this->bindValues($params);
        $this->statement->execute();
        return $this->statement;
    }

    /**
     * 
     * @return \hmvc\Database\Query
     */
    public function createQueryBuilder() {
        return new Query($this);
    }

    /**
     * 
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function execute($input_parameters = null) {
        $this->bindValues($input_parameters);
        $this->sqlCommandHistory[] = $this->statement->queryString;
        $rs = $this->statement->execute();
        $this->rowcount = $this->statement->rowCount();
        $this->queryParams = array();
        return $rs;
    }

    public function getAll($query, $data = null, $fetch_style = PDO::FETCH_ASSOC) {
        $this->connect();
        $this->statement = $this->driver->prepare($this->prepareSQL($query));
        $this->bindValues($data);
        $this->statement->execute();
        $resultset = $this->statement->fetchAll($fetch_style);
        $this->rowcount = $this->statement->rowCount();
        $this->sqlCommandHistory[] = $this->statement->queryString;
        $this->statement->closeCursor();
        return $resultset;
    }

    public function getRow($query, $params = array(), $fetch_style = PDO::FETCH_ASSOC) {
        $this->connect();
        $this->prepare($this->prepareSQL($query), $params);
        $this->execute();
        $this->sqlCommandHistory[] = $this->statement->queryString;
        return $this->first($fetch_style);
    }

    public function getOne($query, $params = array(), $fetch_style = PDO::FETCH_ASSOC) {
        $this->connect();
        $this->prepare($this->prepareSQL($query), $params);
        $this->execute();
        $this->sqlCommandHistory[] = $this->statement->queryString;
        return $this->first($fetch_style);
    }

    public function getScalar($query, $params = array()) {
        $this->connect();
        $this->prepare($this->prepareSQL($query), $params);
        $this->execute();
        $this->sqlCommandHistory[] = $this->statement->queryString;
        return $this->statement->fetchColumn();
    }

    public function begin() {
        $this->connect();
        $this->driver->beginTransaction();
    }

    public function rollback() {
        $this->driver->rollBack();
    }

    public function commit() {
        $this->driver->commit();
    }

    public function errorCode() {
        return $this->driver->errorCode();
    }

    public function errorInfo() {
        return $this->driver->errorInfo();
    }

    /**
     * params
     * @param array $params
     * @return \hmvc\Database\Connection
     */
    public function bindParams($params) {
        if (empty($params)) {
            return;
        }
        if (!is_array($params)) {
            $params = array($params);
        }

        $isassoc = Arr::isAssoc($params);
        foreach ($params as $name => $val) {
            if ($isassoc) {
                $this->bind($name, $val);
            } else {
                $this->bind($name + 1, $val);
            }
        }
        return $this;
    }

    /**
     * values
     * @param type $params
     * @return \hmvc\Database\Connection
     */
    public function bindValues($params) {
        if (empty($params)) {
            return;
        }
        if (!is_array($params)) {
            $params = array($params);
        }

        $isassoc = Arr::isAssoc($params);
        foreach ($params as $name => $val) {
            if ($isassoc) {
                $this->bind($name, $val);
            } else {
                $this->bind($name + 1, $val);
            }
        }
        return $this;
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (gettype($value)) {
                case 'integer':
                case 'double':
                    $type = PDO::PARAM_INT;
                    break;
                case 'boolean':
                    $type = PDO::PARAM_BOOL;
                    break;
                case 'NULL':
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->statement->bindValue(':' . $param, $value, $type);
        return $this;
    }

    public function quote($value) {
        $this->connect();
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (($value = $this->driver->quote($value)) !== false) {
            return $value;
        } else {  // the driver doesn't support quote (e.g. oci)
            return "'" . addcslashes(str_replace("'", "''", $value), "\000\n\r\\\032") . "'";
        }
    }

    public function quoteTable($value) {
        $this->connect();
        return $this->driver->quoteTableName($value);
    }

    public function quoteColumn($value) {
        $this->connect();
        return $this->driver->quoteColumnName($value);
    }

    /**
     * 获取表名
     * @param string $name
     * @return string tableName
     */
    public function tableName($name) {
        return $this->tablePrefix . $name;
    }

    public function isConnected() {
        return $this->isConnected;
    }

    public function lastSql() {
        return end($this->sqlCommandHistory);
    }

    public function allSql() {
        return $this->sqlCommandHistory;
    }

    public function debugDumpParams() {
        return $this->statement->debugDumpParams();
    }

    /**
     * 
     * @return \PDO
     */
    public function pdo() {
        return $this->driver;
    }

    /**
     * 
     * @return \PDOStatement
     */
    public function statement() {
        return $this->statement;
    }

    public function close() {
        $this->driver = null;
        $this->statement = null;
        $this->isConnected = false;
        $this->autoConnect = false;
    }

    /*     * ***********************************************
     * 
     * db helpers
     * 
     * insert delete update
     * ************************************************** */

    public function insert($tableName, $columns) {
        $this->connect();
        $params = array();
        $names = array();
        $placeholders = array();
        foreach ($columns as $name => $value) {
            $names[] = $this->driver->quoteColumnName($name);
            if ($value instanceof Raw) {
                $placeholders[] = $value->raw;
            } else {
                $placeholders[] = ':' . $name;
                $params[$name] = $value;
            }
        }
        $sql = 'INSERT INTO ' . $this->driver->quoteTableName($this->tableName($tableName))
                . ' (' . implode(', ', $names) . ') VALUES ('
                . implode(', ', $placeholders) . ')';
        $this->prepare($sql, $params);
        return $this->execute();
    }

    public function update($table, $columns, $conditions = '', $params = array()) {
        $this->connect();
        $placeholders = array();
        foreach ($columns as $name => $value) {
            if ($value instanceof Raw) {
                $placeholders[] = $this->driver->quoteColumnName($name) . '=' . $value->raw;
                foreach ($value->params as $key => $val) {
                    $this->queryParams[$key] = $val;
                }
            } else {
                $placeholders[] = $this->driver->quoteColumnName($name) . '=:' . $name;
                $this->queryParams[$name] = $value;
            }
        }

        $queryStr = 'UPDATE ' . $this->tableName($table) . ' SET ' . implode(', ', $placeholders);
        if (($where = $this->prepareConditions($conditions, $params)) != '') {
            $queryStr.=' WHERE ' . $where;
        }
        $this->prepare($queryStr, $this->queryParams);
        return $this->execute();
    }

    public function delete($table, $conditions = '', $params = array()) {
        $this->connect();
        $queryStr = 'DELETE FROM ' . $this->driver->quoteTableName($this->tableName($table));
        if (($where = $this->prepareConditions($conditions, $params)) != '') {
            $queryStr.=' WHERE ' . $where;
        }
        $this->prepare($queryStr, $this->queryParams);
        return $this->execute();
    }

    /**
     * 
     * @param string $connectionName
     * @return \hmvc\Database\Connection
     * @throws \Exception
     */
    public static function getConnection($connectionName = 'default') {
        $params = Config::get('database.connections.' . $connectionName);
        if (is_null($params) || !is_array($params)) {
            throw new \Exception('configuration: config/database.php not found!!!');
        }
        return new Connection($params, $connectionName);
    }

    private function prepareConditions($conditions, $params = array()) {

        if (is_array($conditions)) {
            $lines = array();
            foreach ($conditions as $name => $value) {
                if ($value instanceof Raw) {
                    $lines[] = $this->driver->quoteColumnName($name) . '=' . $value->raw;
                } else {
                    $lines[] = $this->driver->quoteColumnName($name) . '=:' . $name;
                    $this->queryParams[$name] = $value;
                }
            }
            return implode(' AND ', $lines);
        } else if (is_string($conditions) && is_array($params)) {
            $lines = array();
            foreach ($params as $name => $value) {
                if ($value instanceof Raw) {
                    $lines[] = $this->driver->quoteColumnName($name) . '=' . $value->raw;
                } else {
                    $lines[] = $this->driver->quoteColumnName($name) . '=:' . $name;
                    $this->queryParams[$name] = $value;
                }
            }
            return implode(' AND ', $lines);
        }
        return '';
    }

    private function prepareSQL($sql) {
        preg_match_all('/\{(.*?)\}/', $sql, $matches);
        if (empty($matches)) {
            return $sql;
        }
        $patterns = array();
        $replacements = array();
        foreach ($matches[1] as $match) {
            $patterns[] = '/\{' . $match . '\}/';
            $replacements[] = $this->driver->quoteTableName($this->tableName($match));
        }

        return preg_replace($patterns, $replacements, $sql);
    }

}
