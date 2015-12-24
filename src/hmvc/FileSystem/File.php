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

namespace hmvc\FileSystem;

/**
 * Description of File
 *
 * @author Administrator
 */
class File {

    public static function isWritable($dir, $chmod = 0755) {
        if (!is_dir($dir) AND ! mkdir($dir, $chmod, TRUE)) {
            return FALSE;
        }
        if (!is_writable($dir) AND ! chmod($dir, $chmod)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * 获取文件扩展名
     * @param string $filename
     * @param boole $withoutDot
     * @return string
     */
    public static function ext($filename, $withoutDot = false) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return $withoutDot ? $ext : ".$ext";
    }

    /**
     * 获取目录所有文件名称
     * @param type $dir
     * @return type
     */
    public static function getFileNames($dir = ".") {

        if (!file_exists($dir) || !is_dir($dir)) {
            return array();
        }
        $dirPath = $dir;
        $dirList = array();
        $dir = opendir($dir);
        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..' && is_file($dirPath . $file)) {
                $dirList[] = $file;
            }
        }
        closedir($dir);
        return $dirList;
    }

    public static function listDir($dir) {
        if (!file_exists($dir) || !is_dir($dir)) {
            return '';
        }
        $dirPath = $dir;
        $dirList = array();
        $dir = opendir($dir);
        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..' && is_dir($dirPath . $file)) {
                $dirList[] = $file;
            }
        }
        closedir($dir);
        return $dirList;
    }

    static public function dirIsWritable($dir, $chmod = 0755) {
        // If it doesn't exist, and can't be made
        if (!is_dir($dir) AND ! mkdir($dir, $chmod, TRUE))
            return FALSE;

        // If it isn't writable, and can't be made writable
        if (!is_writable($dir) AND ! chmod($dir, $chmod))
            return FALSE;

        return TRUE;
    }

    public static function recurseCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    self::recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

}
