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



 * This file is part of the H1Cart package.
 * (w) http://www.h1cart.com
 * (c) Allen Niu <h@h1soft.net>

 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.


 */

namespace hmvc\Helpers;

/**
 * Description of Format
 *
 * @author allen <allen@w4u.cn>
 */
class Format {

    /**
     * byte格式化
     * @param type $size
     * @return type
     */
    public static function FileSize($size) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $u = 0;
        while ((round($size / 1024) > 0) && ($u < 4)) {
            $size = $size / 1024;
            $u++;
        }
        return (number_format($size, 0) . " " . $units[$u]);
    }

    /**
     * 分转元
     * @param type $fen
     * @return type
     */
    public static function FenToYuan($fen = 0, $thousands_sep = ",") {
        return number_format($fen / 100, 2, '.', $thousands_sep);
    }

    /**
     * 元转分
     * @param type $yuan
     * @return type
     */
    public static function YuanToFen($yuan = 0) {
        return intval(round(number_format($yuan, 2, '.', '') * 100));
    }

}
