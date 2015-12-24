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

/**
 * Description of QueryBuilder
 *
 * @author Administrator
 */
class QueryBuilder {

    private $db;
    private $_cur_result_count;
    private $_select_cols = " * ";
    private $_tables;
    private $_joins;
    private $_wheres;
    private $_orderby;
    private $_groupby;
    private $_others;
    private $_limit;
    private $lastSQL;

    public function __construct($connectionName = 'default') {
        $this->db = Connection::getConnection($connectionName);
    }

    /**
     * 
     * @param type $query
     * @param type $data
     * @return type
     * @throws \ErrorException
     */
    public function query($query, $data = false) {
        if (is_array($data)) {
            $query = $this->_buildQueryString($query, $data);
        }
        $result = $this->db->query($query);
        if (!$this->db->errno) {
            return $this->resultToArray($result);
        } else {
            throw new \ErrorException('Error: ' . $this->db->error . '<br />Error No: ' . $this->db->errno . '<br />' . $query);
        }
    }

    public function getAll($tablename, $_where = NULL, $orderby = NULL) {
        $_tbname = $this->tb_name($tablename);
        if (is_array($_where)) {
            foreach ($_where as $key => $value) {
                $this->where($key, $value);
            }
        } else {
            $this->_wheres = ' WHERE 1 ';
        }
        if ($orderby) {
            $orderby = 'order by ' . $orderby;
        }
        $this->lastSQL = "SELECT * FROM {$_tbname} {$this->_wheres} $orderby";
        $this->_resetSql();
        return $this->query($this->lastSQL);
    }

    public function getOne($tablename, $_where) {
        if (!$_where) {
            return NULL;
        }
        $_tbname = $this->tb_name($tablename);
        if (is_array($_where)) {
            foreach ($_where as $key => $value) {
                $this->where($key, $value);
            }

            $this->lastSQL = "SELECT * FROM $_tbname {$this->_wheres}";
            $this->_resetSql();
            return $this->getRow($this->lastSQL);
        }
        return $this->getRow("SELECT * FROM $_tbname WHERE $_where");
    }

    public function scalar($tablename, $colid = 0) {
        $_tbname = $this->tb_name($tablename);
        if (empty($this->_wheres)) {
            return NULL;
        }
        $row = $this->getRow("SELECT {$this->_select_cols} FROM {$_tbname} {$this->_wheres}", MYSQL_NUM);
        $this->_resetSql();
        if ($row && isset($row[$colid])) {
            return $row[$colid];
        }
        return NULL;
    }

    public function getRow($query, $params = MYSQLI_ASSOC, $type = MYSQLI_ASSOC) {
        if (is_array($params)) {
            $query = $this->_buildQueryString($query, $params);
        } else {
            $type = $params;
        }
        $result = $this->db->query($query);
        if (!$this->db->errno) {
            $this->_cur_result_count = $result->num_rows;
            return $result->fetch_array($type);
        } else {
            throw new \ErrorException('Error: ' . $this->db->error . '<br />Error No: ' . $this->db->errno . '<br />' . $query);
        }
    }

    public function exec($query, $data = false) {
        if (is_array($data)) {
            $query = $this->_buildQueryString($query, $data);
        }
        if (!$this->db->query($query)) {
            throw new \Exception($this->db->error);
        }
        $this->_cur_result_count = $this->db->affected_rows;
        return $this->db->affected_rows;
    }

    public function startTranscation() {
        $this->db->autocommit(FALSE);
        return $this;
    }

    public function commit($flags = NULL, $name = NULL) {
        $this->db->commit($flags, $name);
        return $this;
    }

    public function rollback($flags = NULL, $name = NULL) {
        $this->db->rollback($flags, $name);
        return $this;
    }

    public function tables() {
        $result = $this->db->query("SHOW TABLES");
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $rows[] = $row[0];
        }
        return $rows;
    }

    private function resultToArray($result, $resulttype = MYSQLI_ASSOC) {
        if (!$result) {
            return array();
        }
        $this->_cur_result_count = $result->num_rows;
        $rows = array();
        while ($row = $result->fetch_array($resulttype)) {
            $rows[] = $row;
        }
        $result->close();
        return $rows;
    }

    public function affected_rows() {
        return $this->_cur_result_count;
    }

    public function tb_name($_tbname) {
        return $this->_dbconf['prefix'] . $_tbname;
    }

    public function tbname($_tbname) {
        $this->tb_name($_tbname);
    }

    public function insert($_tbname, $_data) {
        if (empty($_data) && !is_array($_data)) {
            return NULL;
        }
        $_tbname = $this->tb_name($_tbname);
        $keys = array();
        $vals = array();
        foreach ($_data as $key => $val) {
            $keys[] = sprintf('`%s`', $this->escape($key));
            if (is_object($val)) {
                $vals[] = sprintf("%s", $val);
            } else {
                $vals[] = sprintf("'%s'", $this->escape($val));
            }
        }
        $query = vsprintf("INSERT INTO `%s` (%s) VALUES (%s)", array(
            $_tbname,
            join(',', $keys),
            join(',', $vals)
        ));
        $this->lastSQL = $query;
        return $this->exec($query);
    }

    public function replace($_tbname, $_data) {
        if (empty($_data) && !is_array($_data)) {
            return NULL;
        }
        $_tbname = $this->tb_name($_tbname);
        $keys = array();
        $vals = array();
        foreach ($_data as $key => $val) {
            $keys[] = sprintf('`%s`', $key);
            $vals[] = sprintf("'%s'", $this->escape($val));
        }
        $query = vsprintf("REPLACE INTO `%s` (%s) VALUES (%s)", array(
            $_tbname,
            join(',', $keys),
            join(',', $vals)
        ));
        return $this->exec($query);
    }

    public function update($_tbname, $_data, $_where = false) {
        if (!empty($_data) && !is_array($_data)) {
            return NULL;
        }
        $_tbname = $this->tb_name($_tbname);


        $vals = array();
        foreach ($_data as $key => $val) {
            $vals[] = sprintf('`%s`=\'%s\'', $key, $this->escape($val));
        }

        if (!$_where) {
            $query = vsprintf("UPDATE `%s` SET %s", array(
                $_tbname,
                join(',', $vals)
            ));
            return $this->exec($query);
        } else {
            if (is_array($_where)) {
                foreach ($_where as $key => $value) {
                    $this->where($key, $value);
                }
                $_where = $this->_wheres;
            } else {
                $_where = " WHERE " . $_where;
            }
            $query = vsprintf("UPDATE `%s` SET %s %s", array(
                $_tbname,
                join(',', $vals),
                $_where
            ));
            $this->_resetSql();
            return $this->exec($query);
        }
    }

    public function delete($_tbname, $_where = false) {
        $_tbname = $this->tb_name($_tbname);
        if (!$_where) {
            $query = "DELETE FROM `$_tbname` {$this->_wheres}";
            $this->lastSQL = $query;
            $this->_resetSql();
            return $this->exec($query);
        } else {
            if (is_array($_where)) {
                foreach ($_where as $key => $value) {
                    $this->where($key, $value);
                }
                $_where = $this->_wheres;
            } else {
                $_where = " WHERE " . $_where;
            }
            $query = vsprintf("DELETE FROM `%s` %s", array(
                $_tbname,
                $_where
            ));
            $this->lastSQL = $query;
            $this->_resetSql();
            return $this->exec($query);
        }
    }

    public function error() {
        return $this->db->error;
    }

    public function escape($str) {
        return $this->db->escape_string($str);
    }

    public function escape_like($str) {
        return $this->db->escape_string($str);
    }

    public function getCharset() {
        return $this->db->get_charset();
    }

    public function autocommit($mode) {
        return $this->db->autocommit($mode);
    }

    public function affected() {
        return $this->db->affected_rows;
    }

    public function lastId() {
        return $this->db->insert_id;
    }

    public function close() {
        if (is_resource($this->db)) {
            $this->db->close();
        }
    }

    private function _buildQueryString($query, $data) {
        if ($data && is_array($data)) {
            $patterns = array();

            $replacements = array();

            foreach ($data as $key => $val) {
                $patterns[] = "/:{$key}/";
                $replacements[] = $this->escape($val);
            }

            $query = preg_replace($patterns, $replacements, $query);
        }
        return $query;
    }

    function __destruct() {
        $this->close();
    }

    public function last_query() {
        return $this->lastSQL;
    }

    public function count($tableName = NULL) {
        $tableName = $this->_buildTableName($tableName);
        $count = 0;
        if ($this->_select_cols == " * ") {
            $this->_select_cols = "COUNT(*) as rowcount";
        }
        if ($tableName) {
            if ($this->_tables) {
                $this->_tables = "{$this->_tables},$tableName";
            } else {
                $this->_tables = "$tableName";
            }
            $this->_buildARSelect();
            $result = $this->getRow($this->lastSQL, MYSQLI_NUM);
            if ($result && isset($result[0])) {
                $count = $result[0];
            }
        } else {
            $this->_buildARSelect();
            $result = $this->getRow($this->lastSQL, MYSQLI_NUM);
            if ($result && isset($result[0])) {
                $count = $result[0];
            }
        }
        $this->_resetSql();
        return $count;
    }

    /**
     * 
     * @param string $tableName 表名
     */
    public function get($tableName = NULL) {
        if ($tableName) {
            $tbAlias = explode(' ', $tableName);
            if (isset($tbAlias[1])) {
                $this->from(trim($tbAlias[0]), trim($tbAlias[1]));
            } else {
                $this->from($tableName);
            }
        }
        $this->_buildARSelect();
        $result = $this->query($this->lastSQL);
        $this->_resetSql();
        return $result;
    }

    private function _resetSql() {
        $this->_select_cols = " * ";
        $this->_tables = "";
        $this->_joins = "";
        $this->_wheres = "";
        $this->_orderby = "";
        $this->_groupby = "";
        $this->_others = "";
        $this->_limit = "";
    }

    public function select($cols = " * ") {
        $this->_select_cols = $cols;
        return $this;
    }

    public function from($tableName, $aliasName = NULL) {
        $tableName = $this->tb_name($tableName);
        if ($this->_tables) {
            $this->_tables = "{$this->_tables},`$tableName` $aliasName";
        } else {
            $this->_tables = "`$tableName` $aliasName";
        }
        return $this;
    }

    public function where($name, $value, $syk = true) {
        if ($syk) {
            $name = "`$name`";
        }
        if (empty($this->_wheres)) {
            $this->_wheres = " WHERE $name='{$this->escape($value)}' ";
        } else {
            $this->_wheres .= " AND $name='{$this->escape($value)}' ";
        }
        return $this;
    }

    public function or_where($name, $value) {
        if (empty($this->_wheres)) {
            $this->_wheres = " WHERE $name='{$this->escape($value)}' ";
        } else {
            $this->_wheres .= " OR $name='{$this->escape($value)}' ";
        }
        return $this;
    }

    public function like($name, $value) {
        $this->_bulidLike($name, $value, 'LIKE', 'AND');
        return $this;
    }

    public function or_like($name, $value) {
        $this->_bulidLike($name, $value, 'LIKE', 'OR');
        return $this;
    }

    public function not_like($name, $value) {
        $this->_bulidLike($name, $value, 'NOT LIKE', 'AND');
        return $this;
    }

    public function or_not_like($name, $value) {
        $this->_bulidLike($name, $value, 'NOT LIKE', 'OR');
        return $this;
    }

    public function order_by($name, $value = "ASC") {
        if (empty($this->_groupby)) {
            $this->_orderby = " ORDER BY $name $value";
        } else {
            $this->_orderby .= ",$name $value ";
        }
        return $this;
    }

    public function group_by($name) {
        if (empty($this->_groupby)) {
            $this->_groupby = " GROUP BY $name";
        } else {
            $this->_groupby .= ",$name";
        }
        return $this;
    }

    private function _bulidLike($name, $value, $type = 'LIKE', $eh = 'AND') {
        if (empty($this->_wheres)) {
            $this->_wheres = " WHERE $name $type '{$this->escape($value)}' ";
        } else {
            $this->_wheres .= " $eh $name $type '{$this->escape($value)}' ";
        }
    }

    public function leftJoin($tableName, $on) {
        $this->_buildJoin($tableName, $on, "LEFT JOIN");
        return $this;
    }

    public function rightJoin($tableName, $on) {
        $this->_buildJoin($tableName, $on, "RIGHT JOIN");
        return $this;
    }

    public function _buildJoin($tableName, $on, $jointype) {
        if (empty($tableName) || empty($on)) {
            return;
        }
        $tbAlias = explode(' ', $tableName);
        $aliasName = '';
        if (isset($tbAlias[1])) {
            $aliasName = trim($tbAlias[1]);
            $tableName = $this->tb_name(trim($tbAlias[0]));
        } else {
            $tableName = $this->tb_name($tableName);
        }
        if ($this->_joins) {
            $this->_joins .= " $jointype `$tableName` $aliasName ON $on ";
        } else {
            $this->_joins = "$jointype `$tableName` $aliasName ON $on ";
        }
    }

    public function join($tableName, $on) {
        if (empty($tableName) || empty($on)) {
            return;
        }
        $tbAlias = explode(' ', $tableName);
        $aliasName = '';
        if (isset($tbAlias[1])) {
            $aliasName = trim($tbAlias[1]);
            $tableName = $this->tb_name(trim($tbAlias[0]));
        } else {
            $tableName = $this->tb_name($tableName);
        }

        if (empty($this->_tables)) {
            $this->_tables = "`$tableName` $aliasName";
        } else {
            $this->_tables .= ",`$tableName` $aliasName";
        }
        if ($this->_wheres) {
            $this->_wheres .= " AND $on ";
        } else {
            $this->_wheres = "WHERE $on ";
        }
        //where
        return $this;
    }

    /**
     * 
     * @param int $pagesize 页大小
     * @param int $offset 偏移量
     * @return \hmvc\Db\Driver\MySQLi
     */
    public function limit($pagesize, $offset = 0) {
        if (empty($this->_limit) && !$offset) {
            $this->_limit = "LIMIT $pagesize";
        } else {
            $this->_limit = "LIMIT $offset,$pagesize";
        }
        return $this;
    }

    public function in($name, $value) {
        $this->_build_in($name, $value, "IN", "AND");
        return $this;
    }

    public function or_in($name, $value) {
        $this->_build_in($name, $value, "IN", "OR");
        return $this;
    }

    public function not_in($name, $value) {
        $this->_build_in($name, $value, "NOT IN", "AND");
        return $this;
    }

    public function or_not_in($name, $value) {
        $this->_build_in($name, $value, "NOT IN", "OR");
        return $this;
    }

    private function _build_in($name, $value, $type = "IN", $eh = "AND") {
        if (empty($value)) {
            return;
        }
        $value = array_map(function($val) {
            return "'$val'";
        }, $value);
        $whestr = join(',', $value);
        if (empty($this->_wheres) && is_array($value)) {
            $this->_wheres = " WHERE $name $type ({$whestr}) ";
        } else if (!empty($this->_wheres) && is_array($value)) {
            $this->_wheres .= " $eh $name $type ({$whestr}) ";
        }
    }

    public function empty_table($tableName) {
        $tableName = $this->tb_name($tableName);
        $this->exec("DELETE FROM $tableName");
    }

    public function truncate($tableName) {
        $tableName = $this->tb_name($tableName);
        $this->exec("TRUNCATE TABLE $tableName");
    }

    private function _buildARSelect() {
        $this->lastSQL = "SELECT {$this->_select_cols} FROM {$this->_tables} {$this->_joins} {$this->_wheres} {$this->_groupby} {$this->_orderby} {$this->_others} {$this->_limit}";
        return $this->lastSQL;
    }

    private function _buildTableName($_tableName) {
        if (!$_tableName) {
            return;
        }
        $tbAlias = explode(' ', $_tableName);
        $aliasName = '';
        if (isset($tbAlias[1])) {
            $aliasName = trim($tbAlias[1]);
            $_tableName = $this->tb_name(trim($tbAlias[0])) . " $aliasName";
        } else {
            $_tableName = $this->tb_name($_tableName);
        }
        return $_tableName;
    }

}
