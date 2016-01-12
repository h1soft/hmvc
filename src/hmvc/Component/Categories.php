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

namespace hmvc\Component;

/**
 * Package hmvc\Component  
 * 
 * Class Categories
 *
 * @author allen <allen@w4u.cn>
 */
use hmvc\Database\Model;

/**
 * 分类
 *
 * @author Allen Niu <a@w4u.cn>
 */
class Categories extends Model {

    protected $table = 'category';
    protected $primaryKey = 'category_id';

    const FORMAT_INDENT = '   ';
    const FORMAT_NODE = '├ ';
    const FORMAT_NODE_END = '└ ';
    const ALL_SUBCATEGORY = 1;
    const ALL_SUBCATEGORY_WITHOUT_SELF = 2;
    const ALL_PARENT_WITHOUT_SELF = 3;
    const ALL_PARENT = 4;

    public function __construct($table = null, $primaryKey = null) {
        if (!is_null($table)) {
            $this->table = $table;
        }
        if (!is_null($table)) {
            $this->primaryKey = $primaryKey;
        }
        parent::__construct();
    }

    public function getCategories($data = array()) {
        $sql = "SELECT node.*, (COUNT(parent.category_id) - 1) AS depth "
                . "FROM {{$this->table}} AS node,{{$this->table}} AS parent "
                . "WHERE node.left_id BETWEEN parent.left_id AND parent.right_id AND parent.status=1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND node.name LIKE '" . $this->db->quote($data['filter_name']) . "%'";
        }

        $sql .= " GROUP BY node.category_id ORDER BY node.left_id";

        if (isset($data['offset']) || isset($data['pagesize'])) {
            if ($data['offset'] < 0) {
                $data['offset'] = 0;
            }

            if ($data['pagesize'] < 1) {
                $data['pagesize'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['offset'] . "," . (int) $data['pagesize'];
        }
        return $this->db->getAll($sql);
    }

    public function getCategory($data = array()) {

        $sql = "SELECT node.*, (COUNT(parent.category_id) - 1) AS depth "
                . "FROM {{$this->table}} AS node,{{$this->table}} AS parent "
                . "WHERE ";
        if (isset($data['category_id'])) {
            $category = $this->get($data['category_id']);
            if (empty($category)) {
                return array();
            }
            $left_id = $category['left_id'];
            $right_id = $category['right_id'];
            $sql .= "node.left_id>=$left_id AND node.right_id<=$right_id";
        } else {
            $sql .= "node.left_id BETWEEN parent.left_id AND parent.right_id ";
        }
        $sql .= " AND parent.status=1";
        if (!empty($data['filter_name'])) {
            $sql .= " AND node.name LIKE '" . $this->db->quote($data['filter_name']) . "%'";
        }

        $sql .= " GROUP BY node.category_id ";
        if (isset($data['depth'])) {
            $sql .= " HAVING depth >= {$data['depth']} ";
        }
        $sql .= " ORDER BY node.left_id";

        if (isset($data['offset']) || isset($data['pagesize'])) {
            if ($data['offset'] < 0) {
                $data['offset'] = 0;
            }

            if ($data['pagesize'] < 1) {
                $data['pagesize'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['offset'] . "," . (int) $data['pagesize'];
        }

        return $this->db->getAll($sql);
    }

    /**
     * 获取分类总数
     * @param type $data
     * @return int
     */
    public function getTotalCategory($data = array()) {
        $sql = "SELECT count(node.category_id) as total "
                . "FROM {{$this->table}} AS node "
                . "WHERE node.status=1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND node.name LIKE '" . $this->db->quote($data['filter_name']) . "%'";
        }


        $result = $this->db->getScalar($sql);
        if ($result) {
            return $result;
        }
        return 0;
    }

    public function addCategory($data) {
        if ($data['parent_id'] == 0) {
            $LeftId = 0;
            $RightId = 1;
        } else {
            $row = $this->checkCategory($data['parent_id']);
            $LeftId = $row['left_id'];
            $RightId = $row['right_id'];
            $this->db->exec("UPDATE {{$this->table}} SET `left_id`=`left_id`+2 WHERE `left_id`>$RightId");
            $this->db->exec("UPDATE {{$this->table}} SET `right_id`=`right_id`+2 WHERE `right_id`>=$RightId");
        }
        if (!isset($data['image'])) {
            $data['image'] = '';
        }
        $data['created_at'] = time();
        $data['updated_at'] = time();
        $data['left_id'] = $RightId;
        $data['right_id'] = $RightId + 1;
        $this->db->insert($this->table, $data);
        return true;
    }

    public function updateCategory($category_id, $data) {
        $category = $this->get($category_id);
        if ($category['parent_id'] != $data['parent_id']) {
//移动分类
            $this->moveCategory($category_id, $data['parent_id']);
        }
        if (!isset($data['image'])) {
            $data['image'] = '';
        }
        $data['updated_at'] = time();
        $this->db->update($this->table, $data, array(
            $this->primaryKey => $category_id
        ));
//        if ($this->db->exec("UPDATE {{$this->table}} SET `name`='" . $data['name'] . "',`parent_id`='" . $data['parent_id'] . "',`image`='" . $data['image'] . "',`updated_at`='" . time() . "' WHERE category_id=$category_id")) {
//            return $this->db->rowCount();
//        } else {
//            return false;
//        }
    }

    /**
     * 
     * @param int $category_id
     * @return type
     */
    function checkCategory($category_id) {
        return $this->db->getOne("select * from {{$this->table}} where category_id=:category_id", array(
                    'category_id' => $category_id
        ));
    }

    /**
     * get category
     * @param int $category_id
     * @return array
     */
    public function get($category_id) {
        return $this->db->getOne("select * from {{$this->table}} where category_id=:category_id", array(
                    'category_id' => $category_id
        ));
    }

    public function getRoot() {
        return $this->db->getRow("select * from {{$this->table}} order by left_id ASC LIMIT 1");
    }

    public function getRootId() {
        $category_id = $this->db->getScalar("select category_id from {{$this->table}} order by left_id ASC LIMIT 1");
        return $category_id ? $category_id : 0;
    }

    /**
     * 删除分类
     * @param type $category_id
     * @return boolean
     */
    function removeCategory($category_id) {
        $row = $this->checkCategory($category_id);
        $left_id = $row['left_id'];
        $right_id = $row['right_id'];
        if ($this->db->exec("DELETE FROM {{$this->table}} WHERE `left_id`>=$left_id AND `right_id`<=$right_id")) {
            $Value = $right_id - $left_id + 1;
//更新左右值 
            $this->db->exec("UPDATE {{$this->table}} SET `left_id`=`left_id`-$Value WHERE `left_id`>$left_id");
            $this->db->exec("UPDATE {{$this->table}} SET `right_id`=`right_id`-$Value WHERE `right_id`>$right_id");
            return true;
        } else {
            return false;
        }
    }

    /**
     * 移动分类
     * @param int $category_id 当前类
     * @param int $parent_id 新的父类
     * @return boolean
     */
    function moveCategory($category_id, $parent_id) {
        $category = $this->get($category_id);
        $parentCategory = $this->get($parent_id);


        $left_id = $category['left_id'];
        $right_id = $category['right_id'];
        $rl_value = $right_id - $left_id;


        $category_list = $this->getCategoryById($category_id, Category::ALL_SUBCATEGORY);
        $category_ids = array();
        foreach ($category_list as $value) {
            $category_ids[] = $value['category_id'];
        }
        $cat_ids = implode(',', $category_ids);


//        $parentLeft = $parentCategory['left_id'];
        $parentRight = $parentCategory['right_id'];

        if ($parentRight > $right_id) {
            $updateLeftSQL = "UPDATE {{$this->table}} SET `left_id`=`left_id`-$rl_value-1 WHERE `left_id`>$right_id AND `right_id`<=$parentRight";
            $updateRightSQL = "UPDATE {{$this->table}} SET `right_id`=`right_id`-$rl_value-1 WHERE `right_id`>$right_id AND `right_id`<$parentRight";
            $pr_val = $parentRight - $right_id - 1;
            $updateSelfSQL = "UPDATE {{$this->table}} SET `left_id`=`left_id`+$pr_val,`right_id`=`right_id`+$pr_val WHERE `category_id` IN($cat_ids)";
        } else {
            $updateLeftSQL = "UPDATE {{$this->table}} SET `left_id`=`left_id`+$rl_value+1 WHERE `left_id`>$parentRight AND `left_id`<$left_id";
            $updateRightSQL = "UPDATE {{$this->table}} SET `right_id`=`right_id`+$rl_value+1 WHERE `right_id`>=$parentRight AND `right_id`<$left_id";
            $pr_val = $left_id - $parentRight;
            $updateSelfSQL = "UPDATE {{$this->table}} SET `left_id`=`left_id`-$pr_val,`right_id`=`right_id`-$pr_val WHERE `category_id` IN($cat_ids)";
        }
        $this->db->exec($updateLeftSQL);
        $this->db->exec($updateRightSQL);
        $this->db->exec($updateSelfSQL);
        return true;
    }

// end func 

    /**
     * 根据ID获取分类
     * @param type $id
     * @param int $type  0 当前分类   
     * @return type
     */
    public function getCategoryById($id, $type = 0) {
        $category = $this->get($id);
        if ($type == 0) {
            return $category;
        }
        $left_id = $category['left_id'];
        $right_id = $category['right_id'];
        $sql = "SELECT * FROM {{$this->table}} WHERE ";
        switch ($type) {
            case Category::ALL_SUBCATEGORY_WITHOUT_SELF://1=所有子类,不包含自己
                $condition = "`left_id`>$left_id AND `right_id`<$right_id";
                break;
            case Category::ALL_SUBCATEGORY://2包含自己的所有子类
                $condition = "`left_id`>=$left_id AND `right_id`<=$right_id";
                break;
            case Category::ALL_PARENT_WITHOUT_SELF://3不包含自己所有父类
                $condition = "`left_id`<$left_id AND `right_id`>$right_id";
                break;
            case Category::ALL_PARENT://4包含自己所有父类 
                $condition = "`left_id`<=$left_id AND `right_id`>=$right_id";
                break;
            default ://所有子类,不包含自己
                $condition = "`left_id`>$left_id AND `right_id`<$right_id";
                break;
        }
        $sql.= $condition . " ORDER BY `left_id` ASC";

        return $this->db->getAll($sql);
    }

    public function getChildCount($category_id) {
        $current = $this->getCategoryById($category_id);
        if (!empty($current)) {
            return (int) ($current['right_id'] - $current['left_id'] - 1) / 2;
        }
    }

    public function getCategoryByRightId($right_id) {
        return $this->db->getRow("select * from {{$this->table}} where right_id=$right_id");
    }

    public function getCategoryByLeftId($left_id) {
        return $this->db->getRow("select * from {{$this->table}} where left_id=$left_id");
    }

    public function getPathById($category_id) {
        $sql = "SELECT parent.name
          FROM {{$this->table}} AS node,
          {{$this->table}} AS parent
          WHERE node.left_id BETWEEN parent.left_id AND parent.right_id
          AND node.category_id=$category_id
          ORDER BY parent.right_id";
        return $this->db->getAll($sql);
    }

    public function getPathByName($name) {
        $sql = "SELECT parent.name
          FROM {{$this->table}} AS node,
          {{$this->table}} AS parent
          WHERE node.left_id BETWEEN parent.left_id AND parent.right_id
          AND node.name='" . $this->db->quote($name) . "'
          ORDER BY parent.right_id";
        return $this->db->getAll($sql);
    }

    /**
     * 分类 向上移动
     * @param int $category_id
     * @return boolean
     */
    public function moveUp($category_id) {
        $current_all_category = $this->getCategoryById($category_id, Category::ALL_SUBCATEGORY);
        if (empty($current_all_category)) {
            return false;
        }
        $current_category = array_shift($current_all_category); //当前分类
        $up_category = $this->getCategoryByRightId($current_category['left_id'] - 1); //UP分类 
        if (empty($up_category)) {
            return false;
        }
        $left_id = $current_category['left_id'];
        $right_id = $current_category['right_id'];
        $up_left_id = $up_category['left_id'];
        $up_right_id = $up_category['right_id'];
        $current_has_subcategory = ($right_id - $left_id != 1); //有子类
        $up_has_subcategory = ($up_right_id - $up_left_id != 1); //有子类
        $batch_commands = array();
//有子类
        if ($current_has_subcategory) {
            $batch_commands[] = "update {{$this->table}} set left_id=$up_left_id,right_id=" . ($right_id - $left_id + $up_left_id) . " where category_id=" . $current_category['category_id'];
            $index = $up_left_id + 1;
            foreach ($current_all_category as $category) {
                $batch_commands[] = "update {{$this->table}} set left_id=" . ($index) . ",right_id=" . ($index + 1) . " where category_id=" . $category['category_id'];
                $index = $index + 2;
            }
        } else { //无子类
            $batch_commands[] = "update {{$this->table}} set left_id=$up_left_id,right_id=" . ($up_left_id + 1) . " where category_id=" . $current_category['category_id'];
        }

        if ($up_has_subcategory) {
            $index = ($right_id - ($up_right_id - $up_left_id));
            $batch_commands[] = "update {{$this->table}} set left_id=" . $index . ",right_id=" . $right_id . " where category_id=" . $up_category['category_id'];
            $index++;
//update all subcategory
            $up_all_category = $this->getCategoryById($up_category['category_id'], Category::ALL_SUBCATEGORY_WITHOUT_SELF);
            foreach ($up_all_category as $category) {
                $batch_commands[] = "update {{$this->table}} set left_id=" . ($index) . ",right_id=" . ($index + 1) . " where category_id=" . $category['category_id'];
                $index = $index + 2;
            }
        } else {
            $index = ($up_left_id + ($right_id - $left_id) + 1);
            $batch_commands[] = "update {{$this->table}} set left_id=$index,right_id=" . ($index + 1) . " where category_id=" . $up_category['category_id'];
        }
        foreach ($batch_commands as $sql) {
//            echo $sql, '<br/>';
            $this->db->exec($sql);
        }
    }

    public function moveDown($category_id) {
        $current_all_category = $this->getCategoryById($category_id, Category::ALL_SUBCATEGORY);
        if (empty($current_all_category)) {
            return false;
        }

        $current_category = array_shift($current_all_category); //当前分类
        $down_category = $this->getCategoryByLeftId($current_category['right_id'] + 1); //Down分类 
        if (empty($down_category)) {
            return false;
        }
        $left_id = $current_category['left_id'];
        $right_id = $current_category['right_id'];
        $down_left_id = $down_category['left_id'];
        $down_right_id = $down_category['right_id'];
        $current_has_subcategory = ($right_id - $left_id != 1); //有子类
        $down_has_subcategory = ($down_right_id - $down_left_id != 1); //有子类
        $batch_commands = array();


        if ($down_has_subcategory) {
            $batch_commands[] = "update {{$this->table}} set left_id=" . $left_id . ",right_id=" . (($down_right_id - $down_left_id) + $left_id) . " where category_id=" . $down_category['category_id'];
            $index = $left_id + 1;
//update all subcategory
            $up_all_category = $this->getCategoryById($down_category['category_id'], Category::ALL_SUBCATEGORY_WITHOUT_SELF);
            foreach ($up_all_category as $category) {
                $batch_commands[] = "update {{$this->table}} set left_id=" . ($index) . ",right_id=" . ($index + 1) . " where category_id=" . $category['category_id'];
                $index = $index + 2;
            }
        } else {
//            $index = ($down_left_id + ($right_id - $left_id) + 1);
            $index = $down_right_id - $down_left_id + 1;
            $batch_commands[] = "update {{$this->table}} set left_id=$left_id,right_id=" . ($index + 1) . " where category_id=" . $down_category['category_id'];
        }

//有子类
        if ($current_has_subcategory) {
            $index = $down_right_id - $down_left_id + $index;
            $batch_commands[] = "update {{$this->table}} set left_id=" . ($index + 1) . ",right_id=" . ($down_right_id) . " where category_id=" . $current_category['category_id'];
            $index++;
            foreach ($current_all_category as $category) {
                $batch_commands[] = "update {{$this->table}} set left_id=" . ($index + 1) . ",right_id=" . ($index + 2) . " where category_id=" . $category['category_id'];
                $index = $index + 2;
            }
        } else { //无子类
            $batch_commands[] = "update {{$this->table}} set left_id=" . ($down_right_id - 1) . ",right_id=" . ($down_right_id) . " where category_id=" . $current_category['category_id'];
        }


        foreach ($batch_commands as $sql) {
//            echo $sql, '<br/>';
            $this->db->exec($sql);
        }
    }

    function getTree($data, $pId) {
        $tree = array();
        foreach ($data as $k => $v) {
            if ($v['parent_id'] == $pId) {
                $v['parent_id'] = $this->getTree($data, $v['category_id']);
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public function getULTree($data, $pId) {
        $html = '';
        foreach ($data as $v) {
            if ($v['parent_id'] == $pId) {
                $html .= "<li id='node_" . $v['category_id'] . "' data-id='" . $v['category_id'] . "'>" . $v['name'];
                $html .= $this->getULTree($data, $v['category_id']);
                $html = $html . "</li>";
            }
        }
        return $html ? '<ul>' . $html . '</ul>' : $html;
    }

    /*
      顺序显示
      SELECT node.image
      FROM h_category AS node,
      h_category AS parent
      WHERE node.left_id BETWEEN parent.left_id AND parent.right_id
      AND parent.category_id = 1
      ORDER BY node.left_id;
     * 
     */

    /**
     * ok
     */
    /*
     * 
      SELECT node.image, (COUNT(parent.image) - 1) AS depth
      FROM h_category AS node,
      h_category AS parent
      WHERE node.left_id BETWEEN parent.left_id AND parent.right_id
      GROUP BY node.image
      ORDER BY node.left_id;
     */

    /*
      DROP TABLE IF EXISTS `getssl_category`;
      CREATE TABLE `getssl_category` (
      `category_id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) DEFAULT NULL,
      `description` mediumtext,
      `image` varchar(255) DEFAULT NULL,
      `parent_id` int(11) NOT NULL,
      `left_id` int(11) DEFAULT NULL,
      `right_id` int(11) DEFAULT NULL,
      `status` tinyint(1) NOT NULL DEFAULT '1',
      `created_at` int(11) NOT NULL,
      `updated_at` int(11) NOT NULL,
      PRIMARY KEY (`category_id`),
      KEY `left_id` (`left_id`),
      KEY `right_id` (`right_id`),
      KEY `status` (`status`)
      ) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
      INSERT INTO `getssl_category` VALUES (1,'root',NULL,'',0,1,10,1,1406309100,1406471505)
     */
}
