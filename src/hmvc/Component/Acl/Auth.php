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

namespace hmvc\Component\Acl;

use hmvc\Database\Connection;

/**
 * Package hmvc\Component\Acl  Auth
 *
 * @author allen <allen@w4u.cn>
 */
class Auth {

    protected $userTableName = 'user_permissions';
    protected $groupTableName = 'group_permissions';

    /**
     *
     * @var hmvc\Database\Connection 
     */
    protected $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    function hasPermission($permission, $userid, $group_id) {

        if (!$this->userPermissions($permission, $userid)) {
            return false;
        }

        if (!$this->groupPermissions($permission, $group_id)) {
            return false;
        }

        return true;
    }

    protected function userPermissions($permission, $userid) {
        $tablename = $this->db->tableName($this->userTableName);
        $user = $this->db->prepare("SELECT * FROM {$tablename} WHERE permission_name=:permission_name AND userid=:userid", array(
                    'permission_name' => $permission,
                    'userid' => $userid
                ))->first();

        if (isset($user['permission_type']) && $user['permission_type'] == 'no') {
            return false;
        }
        return true;
    }

    protected function groupPermissions($permission, $group_id) {
        $tablename = $this->db->tableName($this->groupTableName);
        $group = $this->db->prepare("SELECT * FROM {$tablename} WHERE permission_name=:permission_name AND userid=:userid", array(
                    'permission_name' => $permission,
                    'userid' => $group_id
                ))->first();

        if (isset($group['permission_type']) && $group['permission_type'] == 'no') {
            return false;
        }
        return true;
    }

}
