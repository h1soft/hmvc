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

use hmvc\Core\Application;

/**
 * Description of DB
 *
 * @author Administrator
 */
class DB {

    /**
     * 
     * @param type $name
     * @return Query
     */
    public static function table($name) {
        $query = new Query(Application::getInstance()->get('db'));
        return $query->from($name);
    }

    /**
     * 
     * @param type $table
     * @param type $params
     * @return \hmvc\Database\Query
     */
    public static function insert($table, $params) {
        $query = new Query(Application::getInstance()->get('db'));
        $query->insert($table, $params);
        return $query;
    }

    /**
     * 
     * @param type $table
     * @param type $columns
     * @param type $conditions
     * @param type $params
     * @return \hmvc\Database\Query
     */
    public static function update($table, $columns, $conditions = '', $params = array()) {
        $query = new Query(Application::getInstance()->get('db'));
        $query->update($table, $columns, $conditions, $params);
        return $query;
    }

    /**
     * 
     * @param type $table
     * @param type $conditions
     * @param type $params
     * @return \hmvc\Database\Query
     */
    public static function delete($table, $conditions = '', $params = array()) {
        $query = new Query(Application::getInstance()->get('db'));
        $query->delete($table, $conditions, $params);
        return $query;
    }

    /**
     * 
     * @param type $callable
     * @param type $connectionName
     * @return \hmvc\Database\Query
     */
    public static function using($callable, $connectionName = 'default') {
        if (is_callable($callable)) {
            $db = Application::getInstance()->get('db')->using($connectionName);
            call_user_func_array($callable, array($db, new Query($db)));
        } else if (is_string($callable)) {
            $db = Application::getInstance()->get('db');
            return new Query($db->using($callable));
        }
    }

    /**
     * 
     * @param type $connectionName
     * @return \hmvc\Database\Query
     */
    public static function createQuery($connectionName = 'default') {
        return new Query(Application::getInstance()->get('db')->using($connectionName));
    }

}
