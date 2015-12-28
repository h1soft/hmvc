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

/**
 * Package hmvc\Component\Acl  Auth
 *
 * @author allen <allen@w4u.cn>
 */
class Auth {

    function check($permission, $userid, $group_id) {

        //we check the user permissions first
        If (!$this->user_permissions($permission, $userid)) {
            return false;
        }

        if (!$this->group_permissions($permission, $group_id) & $this->IsUserEmpty()) {
            return false;
        }

        return true;
    }

    function user_permissions($permission, $userid) {
        $this->db->q("SELECT COUNT(*) AS count FROM user_permissions WHERE permission_name='$permission' AND userid='$userid' ");
        $result = \hmvc\Database\DB::table('user_permissions')->where('permission_name=:permission_name AND userid=:userid', array(
            'permission_name' => $permission,
            'userid' => $userid
        ))->get();

       if(empty($result)){
            $this->db->q("SELECT * FROM user_permissions WHERE permission_name='$permission' AND userid='$userid' ");
            If ($f['permission_type'] == 0) {
                return false;
            }
            return true;
        }
        return true;
    }

    function group_permissions($permission, $group_id) {
        $this->db->q("SELECT COUNT(*) AS count FROM group_permissions WHERE permission_name='$permission' AND group_id='$group_id' ");

        $f = $this->db->f();

        if ($f['count'] > 0) {
            $this->db->q("SELECT * FROM group_permissions WHERE permission_name='$permission' AND group_id='$group_id' ");

            $f = $this->db->f();

            If ($f['permission_type'] == 0) {
                return false;
            }

            return true;
        }

        return true;
    }

    function setUserEmpty($val) {
        $this->userEmpty = $val;
    }

    function isUserEmpty() {
        return $this->userEmpty;
    }

}
