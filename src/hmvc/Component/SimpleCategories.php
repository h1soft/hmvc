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

/*
  Table structure
  _________________________________________
  CREATE TABLE IF NOT EXISTS `categories` (
  `c_id` int(11) NOT NULL AUTO_INCREMENT,
  `c_name` varchar(255) DEFAULT NULL,
  `c_parent` int(11) DEFAULT '0',
  `c_order` int(11) DEFAULT '0',
  PRIMARY KEY (`c_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;
  _________________________________________
  INSERT INTO `categories`
  (`c_id`, `c_name`, `c_parent`, `c_order`)
  VALUES
  (1, 'first category', 0, 0),
  (2, 'second category', 3, 0),
  (3, 'third category', 0, 0),
  (4, 'fourth category', 2, 0),
  (5, 'fifth category', 1, 0);

 */

/**
 * Package hmvc\Component  
 * 
 * Class SimpleCategories
 *
 * @author allen <allen@w4u.cn>
 */
class SimpleCategories {

    public function __construct() {
        
    }

    /* This function generate HTML output from the recursive array of categories */

    public static function generateMenu($categories, $level) {

        echo '<ol id="level_' . $level . '">';
        foreach ($categories as $category) {

            echo '<li id="item_' . $category->c_id . '" >';
            echo '<div>' . $category->c_name . '</div>';
            if (isset($category->children) and count($category->children) > 0) {
                netstedCategories::generateMenu($category->children, $category->c_id);
            }
            echo '</li>';
        }
        echo '</ol>';
    }

    public static function buildMenu() {

        $categories = array();
        $query = mysql_query('select * from  categories order by c_order asc') or die(mysql_error());
        while ($row = mysql_fetch_object($query)) {
            $categories[] = $row;
        }
        //we start with root categories first
        $nestedCategories = netstedCategories::getChildren($categories, 0);
        netstedCategories::generateMenu($nestedCategories, 0);
    }

    /* Check if a category has children by passing total categories and parent id */

    public static function hasChildren($categories, $parent_id) {

        foreach ($categories as $category) {
            //if we found any single category that parent id is equal to given category id
            if ($category->c_parent == $parent_id)
                return true;
        }

        //if we won't find anything we return false;
        return false;
    }

    /* A recursive public static function that is responsible to build nested array */

    public static function getChildren($categories, $parent_id) {

        $temp = array();

        foreach ($categories as $category) {

            /* We take care about given parent id ,remaning we skip */
            if ($category->c_parent == $parent_id) {

                if (netstedCategories::hasChildren($categories, $category->c_id)) {
                    /* if present category has children we call this function again by passing categories
                      array and current category id */
                    $category->children = netstedCategories::getChildren($categories, $category->c_id);
                }

                $temp[] = $category;
            }
        }

        return $temp;
    }

}
