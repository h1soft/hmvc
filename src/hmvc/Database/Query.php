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

use hmvc\Database\Connection;
use hmvc\Helpers\Str;

/**
 * ##demo
 * 
 * $rs = DB::table('user')->where('name', 'like', '%b')->where(function($query) {
 * $query->orWhere('Id', 29);
 * $query->orWhere('Id', 31);
 * $query->orWhere('Id', 33);
 * })->getSQL();
 */

/**
 * Description of Query
 *
 * @author allen <allen@w4u.cn>
 */
class Query {

    /**
     *
     * @var string (SELECT|DELETE|UPDATE)
     */
    protected $sqlType = 'SELECT';
    protected $query = array();
    protected $select = array();
    protected $fields = array();

    /**
     *
     * @var array tables
     */
    protected $from = array();

    /**
     *
     * @var array wheres
     */
    protected $where = array();

    /**
     *
     * @var array  havings
     */
    protected $having = array();

    /**
     *
     * @var array joins
     */
    protected $join = array();
    protected $params = array();
    protected $orderBy = array();
    protected $groupBy = array();
    protected $limit = 0;
    protected $offset = 0;
    protected $distinct = false;

    /**
     *
     * @var \hmvc\Database\Driver
     */
    private $driver;

    /**
     *
     * @var string SQL OPTION (EXPLAIN)
     */
    protected $preoption = '';
    protected $isclosureWhere = false;

    /**
     *
     * @var Connection
     */
    protected $db;

    public function __construct(Connection $connection = null) {
        $this->db = $connection;
        $connection->connect();
        $this->driver = $connection->getDriver();
    }

    /**
     * Add statement for select - SELECT [?] FROM ...
     *
     * Examples:
     * $sql->select("u.*")
     *     ->select("b.*, COUNT(*) as total")
     *
     * @param string $columns
     * @return Query
     */
    public function select($columns = '*') {
        if (is_string($columns)) {
            $this->select[] = $columns;
        } else if (is_array($columns)) {
            $this->select[] = implode(',', $columns);
        }
        return $this;
    }

    /**
     * 
     * @param type $tables
     * @return \hmvc\Database\Query
     */
    public function from($tables) {
        $tables = is_array($tables) ? $tables : func_get_args();
        foreach ($tables as $table) {
            $this->from[] = $this->db->tableName($table);
        }
        return $this;
    }

    /**
     * add where to statement
     * @param string $name
     * @param string $operator (in|not in|<>|=|!=|....)
     * @param string|array $value
     * @return \hmvc\Database\Query
     */
    function where($name, $operator = '=', $value = null) {
        if (is_callable($name)) {
            $this->isclosureWhere = true;
            $this->_addWhere('(', 'AND');
            call_user_func($name, $this);
            $this->where[] = ')';
            $this->isclosureWhere = false;
            return $this;
        }
        $where = $this->driver->quoteColumnName($name);
        $paramName = Str::random(Str::ALPHA);
        $colName = ':' . $paramName;

        switch (strtoupper($operator)) {
            case 'IN':
            case 'NOT IN':
                if (is_array($value)) {
                    $value = implode(',', $value);
                    $where .= ' ' . $operator . ' (' . $value . ')';
                } elseif ($value instanceof Raw) {
                    $where .= ' ' . $operator . ' (' . $value->raw . ')';
                } else {
                    $where .= ' ' . $operator . ' (' . $colName . ')';
                }

                break;
            case 'NOT LIKE':
            case 'LIKE':
                $where .= ' ' . $operator . ' ' . $colName . ' ';
                break;
            case '=':
            case '!=':
            case '<>':
            case '<=>':
            case '>':
            case '<':
            case '<=':
            case '>=':
                if ($value instanceof Raw) {
                    $where .= ' ' . $operator . $value->raw;
                } else {
                    $where .= $operator . $colName;
                }

                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                $value = NULL;
                $where .= ' ' . $operator;
                break;
            default:
                $value = $operator;
                $where .= '=' . $colName;
                break;
        }
        $this->_addWhere($where, 'AND');
        if (!is_null($value) || !$value instanceof Raw) {
            $this->params[$paramName] = $value;
        }
        return $this;
    }

    function orWhere($name, $operator = '=', $value = null) {
        if (is_callable($name)) {
            $this->isclosureWhere = true;
            $this->_addWhere('(', 'OR');
            call_user_func($name, $this);
            $this->where[] = ')';
            $this->isclosureWhere = false;
            return $this;
        }
        $where = $this->driver->quoteColumnName($name);
        $paramName = Str::random(Str::ALPHA);
        $colName = ':' . $paramName;

        switch (strtoupper($operator)) {
            case 'IN':
            case 'NOT IN':
                if (is_array($value)) {
                    $value = implode(',', $value);
                    $where .= ' ' . $operator . ' (' . $value . ')';
                } elseif ($value instanceof Raw) {
                    $where .= ' ' . $operator . ' (' . $value->raw . ')';
                } else {
                    $where .= ' ' . $operator . ' (' . $colName . ')';
                }

                break;
            case 'NOT LIKE':
            case 'LIKE':
                $where .= ' ' . $operator . ' ' . $colName . ' ';
                break;
            case '=':
            case '!=':
            case '<>':
            case '<=>':
            case '>':
            case '<':
            case '<=':
            case '>=':
                if ($value instanceof Raw) {
                    $where .= ' ' . $operator . $value->raw;
                } else {
                    $where .= $operator . $colName;
                }

                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                $value = NULL;
                $where .= ' ' . $operator;
                break;
            default:
                $value = $operator;
                $where .= '=' . $colName;
                break;
        }
        $this->_addWhere($where, 'OR');
        if (!is_null($value) || !$value instanceof Raw) {
            $this->params[$paramName] = $value;
        }

        return $this;
    }

    /**
     * 
     * @param type $conditions
     * @param type $params
     * @return \hmvc\Database\Query
     */
    function rawWhere($conditions, $params = array()) {
        $this->where[] = $conditions;
        $this->addParams($params);
        return $this;
    }

    /**
     * Add where in statement
     *
     * @param string $column
     * @param array $params
     *
     * @return Query
     */
    public function whereIn($column, $params) {
        $this->prepareWhereInStatement($column, $params, false);
        return $this;
    }

    /**
     * Add where not in statement
     *
     * @param $column
     * @param $params
     * @return Query
     */
    public function whereNotIn($column, $params) {
        $this->prepareWhereInStatement($column, $params, true);
        return $this;
    }

    private function _addWhere($where, $type = 'AND') {
        if (empty($this->where) || end($this->where) == '(') {
            $this->where[] = $where;
        } else {
            $this->where[] = $type . ' ';
            $this->where[] = $where;
        }
    }

    /**
     * 
     * @param type $statement
     * @param type $params
     * @return \hmvc\Database\Query
     */
    public function having($statement, $params = null) {
        $this->having[] = $statement;
        $this->addParams($params);
        return $this;
    }

    public function join($type, $table, $on, $params = array()) {
        $table = $this->db->tableName($table);
        $type = strtoupper($type);
        $this->join[] = "$type $table ON $on";
        $this->addParams($params);
        return $this;
    }

    public function leftJoin($table, $on, $params = array()) {
        $this->join('LEFT JOIN', $table, $on, $params);
        return $this;
    }

    public function rightJoin($table, $on, $params = array()) {
        $this->join('RIGHT JOIN', $table, $on, $params);
        return $this;
    }

    public function innerJoin($table, $on, $params = array()) {
        $this->join('INNER JOIN', $table, $on, $params);
        return $this;
    }

    public function groupBy($statement) {
        $this->groupBy[] = $statement;
        return $this;
    }

    public function orderBy($statement) {
        $this->orderBy[] = $statement;
        return $this;
    }

    /**
     * Returns generated SQL query
     *
     * @return string
     */
    public function getSQL() {
        $sql = '';
        switch ($this->sqlType) {
            case 'SELECT':
                $sql .= 'SELECT ' . $this->prepareSelectString() . $this->prepareFrom();
                break;
            case 'DELETE FROM':
                $sql .= 'DELETE FROM ' . $this->prepareFrom();
                break;
            case 'UPDATE':
                $sql .= 'UPDATE ' . $this->prepareFrom() . $this->prepareUpdateSet();
                break;
            default:
                break;
        }
        $sql .= $this->prepareJoinString();
        $sql .= $this->prepareWhereString();
        $sql .= $this->prepareGroupByString();
        $sql .= $this->prepareHavingString();
        $sql .= $this->prepareOrderByString();
        $sql .= $this->prepareLimitString();
        return $sql;
    }

    /**
     * Execute built query
     * This will prepare query, bind params and execute query
     *
     * @return Statement
     */
    public function execute() {
        $this->db->prepare($this->getSQL(), $this->params);
        return $this->db->execute();
    }

    public function get($fetch_style = \PDO::FETCH_ASSOC) {
        $this->db->prepare($this->getSQL(), $this->params);
        return $this->db->results($fetch_style);
    }

    public function first($fetch_style = \PDO::FETCH_ASSOC) {
        $this->db->prepare($this->getSQL(), $this->params);
        $this->db->execute();
        return $this->db->first($fetch_style);
    }

    public function count($columnName = '*') {
        $sql = "SELECT count($columnName) as rowcount FROM " . $this->prepareFrom();
        $sql .= $this->prepareJoinString();
        $sql .= $this->prepareWhereString();
        $sql .= $this->prepareGroupByString();
        $sql .= $this->prepareHavingString();
        $sql .= $this->prepareOrderByString();
        $sql .= $this->prepareLimitString();
        return $this->db->getScalar($sql, $this->params);
    }

    public function rowCount() {
        return $this->db->rowCount();
    }

    public function setDistinct($distinct) {
        $this->distinct = $distinct;
    }

    /**
     * Add param(s) to stack
     *
     * @param array $params
     *
     * @return void
     */
    public function addParams($params) {
        if (is_null($params)) {
            return;
        }
        if (!is_array($params)) {
            $params = array($params);
        }
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * update set
     * @param type $name
     * @param type $value
     * @return \hmvc\Database\Query
     */
    public function set($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->fields[] = "$key=:$key";
                $this->params[$key] = $val;
            }
        } else {
            $this->fields[] = "$name=:$name";
            $this->params[$name] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    public function bindValues($params) {
        $this->addParams($params);
        return $this;
    }

    public function bind($name, $value) {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Returns prepared select string
     *
     * @return string
     */
    private function prepareSelectString() {
        if (empty($this->select)) {
            $this->select("*");
        }
        return $this->distinct ? 'distinct ' . implode(", ", $this->select) . ' FROM ' : implode(", ", $this->select) . ' FROM ';
    }

    private function prepareFrom() {
        return implode(", ", $this->from) . " ";
    }

    private function prepareUpdateSet() {
        return ' SET ' . implode(",", $this->fields);
    }

    /**
     * Prepares where in statement
     *
     * @param string $column
     * @param array $params
     * @param bool $not_in Use NOT IN statement
     *
     * @return void
     */
    private function prepareWhereInStatement($column, $params, $not_in = false) {
        $in = ($not_in) ? "NOT IN" : "IN";
        if (!is_array($params)) {
            $params = array($params);
        }
        $this->where[] = $this->driver->quoteColumnName($column) . " " . $in . ' (' . implode(',', $params) . ')';
    }

    /**
     * Returns prepared join string
     *
     * @return string
     */
    private function prepareJoinString() {
        if (!empty($this->join)) {
            return implode(" ", $this->join) . " ";
        }
        return '';
    }

    /**
     * Returns prepared where string
     *
     * @return string
     */
    private function prepareWhereString() {
        $where = '';
        if (!empty($this->where)) {
            $where = ' WHERE ' . implode(' ', $this->where);
        }
        return $where;
    }

    /**
     * Returns prepared group by string
     *
     * @return string
     */
    private function prepareGroupByString() {
        if (!empty($this->groupBy)) {
            return " GROUP BY " . implode(", ", $this->groupBy) . " ";
        }
        return '';
    }

    /**
     * Returns prepared having string
     *
     * @return string
     */
    private function prepareHavingString() {
        if (!empty($this->having)) {
            return " HAVING " . implode(", ", $this->having) . " ";
        }
        return '';
    }

    /**
     * Returns prepared order by string
     *
     * @return string
     */
    private function prepareOrderByString() {
        if (!empty($this->orderBy)) {
            return " ORDER BY " . implode(", ", $this->orderBy) . " ";
        }
        return '';
    }

    /**
     * Returns prepared limit string
     *
     * @return string
     */
    private function prepareLimitString() {
        if (!empty($this->limit) && empty($this->offset)) {
            return " LIMIT {$this->limit}";
        } else if ($this->offset) {
            return " LIMIT {$this->offset},{$this->limit}";
        }
        return '';
    }

    /**
     * Add statement for limit
     *
     * Examples:
     * $sql->limit(10);
     * $sql->limit(0,10);
     *
     * @param int $limit
     * @param int $offset
     * @return Query
     */
    public function limit($limit, $offset = null) {
        $this->limit = $limit;
        if ($offset) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function reset() {
        $this->query = array();
        $this->select = array();
        $this->from = array();
        $this->where = array();
        $this->having = array();
        $this->join = array();
        $this->params = array();
        $this->orderBy = array();
        $this->groupBy = array();
        $this->limit = 0;
        $this->offset = 0;
    }

    public function insert($tableName, $columns) {
        return $this->db->insert($tableName, $columns);
    }

    public function update($table = NULL, $columns = NULL, $conditions = '', $params = array()) {
        if (is_null($table)) {
            $this->sqlType = 'UPDATE';
            $this->execute();
            return $this->db->rowCount();
        }
        return $this->db->update($table, $columns, $conditions, $params);
    }

    public function delete($table = NULL, $conditions = '', $params = array()) {
        if (is_null($table)) {
            $this->sqlType = 'DELETE FROM';
            $this->execute();
            return $this->db->rowCount();
        }
        return $this->db->delete($table, $conditions, $params);
    }

}
