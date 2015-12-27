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

namespace hmvc\Database;

use hmvc\Database\Connection;

/**
 * Description of Query
 *
 * @author Administrator
 */
class Query {

    protected $sqlType = 'SELECT';
    protected $query = array();
    protected $select = array();
    protected $fields = array();
    protected $from = array();
    protected $where = array();
    protected $having = array();
    protected $join = array();
    protected $params = array();
    protected $orderBy = array();
    protected $groupBy = array();
    protected $limit = 0;
    protected $offset = 0;
    private $driver;
    protected $preoption = '';

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
        if ($columns == '*' || (is_string($columns) && strpos($columns, '(') !== false)) {
            $this->select[] = $columns;
        } else {
            if (!is_array($columns)) {
                $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            }
            foreach ($columns as $i => $column) {
                if (is_object($column)) {
                    $columns[$i] = (string) $column;
                } elseif (strpos($column, '(') === false) {
                    if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $column, $matches)) {
                        $columns[$i] = $this->driver->quoteColumnName($matches[1]) . ' AS ' . $this->driver->quoteColumnName($matches[2]);
                    } else {

                        $columns[$i] = $this->driver->quoteColumnName($column);
                    }
                }
            }
            $this->select[] = implode(', ', $columns);
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
     * Add statement for where - ... WHERE [?] ...
     *
     * Examples:
     * $sql->where("user_id = ?", $user_id);
     * $sql->where("u.registered > ? AND (u.is_active = ? OR u.column IS NOT NULL)", array($registered, 1));
     *
     * @param string $conditions
     * @param mixed $params
     * @return Query
     */
    function where($conditions, $params = array()) {
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

    /**
     * Add statement for HAVING ...
     * @param string $statement
     * @param mixed $params
     * @return Query
     */
    public function having($statement, $params = null) {
        $this->having[] = $statement;
        $this->addParams($params);
        return $this;
    }

    /**
     * Add statement for join
     *
     * Examples:
     * $sql->join("INNER JOIN posts p ON p.user_id = u.user_id")
     *
     * @param string $statement
     * @return Query
     */
    public function join($type, $table, $on, $params = array()) {
        $table = $this->db->tableName($table);
        $type = strtoupper($type);
        $this->join[] = "$type $table $on";
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

    /**
     * Add statement for group - GROUP BY [...]
     *
     * Examples:
     * $sql->groupBy("user_id");
     * $sql->groupBy("u.is_active, p.post_id");
     *
     * @param string $statement
     * @return Query
     */
    public function groupBy($statement) {
        $this->groupBy[] = $statement;
        return $this;
    }

    /**
     * Add statement for order - ORDER BY [...]
     *
     * Examples:
     * $sql->orderBy("registered");
     * $sql->orderBy("is_active, registered DESC");
     *
     * @param string $statement
     * @return Query
     */
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

    public function one() {
        $this->db->prepare($this->getSQL(), $this->params);
        $this->db->execute();
        return $this->db->row();
    }

    public function count() {
        return $this->db->rowCount();
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
                $this->params[':' . $key] = $val;
            }
        } else {
            $this->fields[] = "$name=:$name";
            $this->params[':' . $name] = $value;
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
        return implode(", ", $this->select) . " FROM ";
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
        if (!empty($this->where)) {
            return " WHERE " . implode(" AND ", $this->where) . " ";
        }
        return '';
    }

    /**
     * Returns prepared group by string
     *
     * @return string
     */
    private function prepareGroupByString() {
        if (!empty($this->groupBy)) {
            return "GROUP BY " . implode(", ", $this->groupBy) . " ";
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
            return "HAVING " . implode(", ", $this->having) . " ";
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
            return "ORDER BY " . implode(", ", $this->orderBy) . " ";
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
            return "LIMIT {$this->limit}";
        } else if ($this->offset) {
            return "LIMIT {$this->limit},{$this->offset}";
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

namespace hmvc\Database;

use hmvc\Database\Connection;

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
    /**
     *
     * @var \PDO
     */
    private $driver;
    /**
     *
     * @var string SQL OPTION (EXPLAIN)
     */
    protected $preoption = '';

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
        if ($columns == '*' || (is_string($columns) && strpos($columns, '(') !== false)) {
            $this->select[] = $columns;
        } else {
            if (!is_array($columns)) {
                $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            }
            foreach ($columns as $i => $column) {
                if (is_object($column)) {
                    $columns[$i] = (string) $column;
                } elseif (strpos($column, '(') === false) {
                    if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $column, $matches)) {
                        $columns[$i] = $this->driver->quoteColumnName($matches[1]) . ' AS ' . $this->driver->quoteColumnName($matches[2]);
                    } else {

                        $columns[$i] = $this->driver->quoteColumnName($column);
                    }
                }
            }
            $this->select[] = implode(', ', $columns);
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
     * Add statement for where - ... WHERE [?] ...
     *
     * Examples:
     * $sql->where("user_id = ?", $user_id);
     * $sql->where("u.registered > ? AND (u.is_active = ? OR u.column IS NOT NULL)", array($registered, 1));
     *
     * @param string $conditions
     * @param mixed $params
     * @return Query
     */
    function where($conditions, $params = array()) {
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

    /**
     * Add statement for HAVING ...
     * @param string $statement
     * @param mixed $params
     * @return Query
     */
    public function having($statement, $params = null) {
        $this->having[] = $statement;
        $this->addParams($params);
        return $this;
    }

    /**
     * Add statement for join
     *
     * Examples:
     * $sql->join("INNER JOIN posts p ON p.user_id = u.user_id")
     *
     * @param string $statement
     * @return Query
     */
    public function join($type, $table, $on, $params = array()) {
        $table = $this->db->tableName($table);
        $type = strtoupper($type);
        $this->join[] = "$type $table $on";
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

    /**
     * Add statement for group - GROUP BY [...]
     *
     * Examples:
     * $sql->groupBy("user_id");
     * $sql->groupBy("u.is_active, p.post_id");
     *
     * @param string $statement
     * @return Query
     */
    public function groupBy($statement) {
        $this->groupBy[] = $statement;
        return $this;
    }

    /**
     * Add statement for order - ORDER BY [...]
     *
     * Examples:
     * $sql->orderBy("registered");
     * $sql->orderBy("is_active, registered DESC");
     *
     * @param string $statement
     * @return Query
     */
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

    public function one() {
        $this->db->prepare($this->getSQL(), $this->params);
        $this->db->execute();
        return $this->db->row();
    }

    public function count() {
        return $this->db->rowCount();
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
                $this->params[':' . $key] = $val;
            }
        } else {
            $this->fields[] = "$name=:$name";
            $this->params[':' . $name] = $value;
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
        return implode(", ", $this->select) . " FROM ";
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
        if (!empty($this->where)) {
            return " WHERE " . implode(" AND ", $this->where) . " ";
        }
        return '';
    }

    /**
     * Returns prepared group by string
     *
     * @return string
     */
    private function prepareGroupByString() {
        if (!empty($this->groupBy)) {
            return "GROUP BY " . implode(", ", $this->groupBy) . " ";
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
            return "HAVING " . implode(", ", $this->having) . " ";
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
            return "ORDER BY " . implode(", ", $this->orderBy) . " ";
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
            return "LIMIT {$this->limit}";
        } else if ($this->offset) {
            return "LIMIT {$this->limit},{$this->offset}";
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
>>>>>>> origin/master
