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

namespace hmvc\Helpers;

/**
 * Description of Str
 *
 * @author Administrator
 */
final class Str {

    const NUM = 0;
    const ALNUM = 1;
    const NUMBERIC = 2;
    const ALPHA = 3;
    const MD5 = 4;
    const SHA1 = 5;
    const UUID = 6;
    const UNIQUE = 7;
    const HEXDEC = 8;
    const NOZERO = 9;
    const DISTINCT = 10;

    public static function token() {
        return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(TRUE)));
    }

    /**
     * 随机生成一个数字
     * @param int $min 最小
     * @param int $max 最大值
     * @return int
     */
    public static function int($min = 0, $max = RAND_MAX) {
        return mt_rand($min, $max);
    }

    public static function simpleRandom($length = 6) {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $offset = (62 - $length) - mt_rand(0, 62 - $length);
        return substr(str_shuffle($pool), $offset, $length);
    }

    /**
     * 随机生成
     * @param int $type 类型
     * @param int $length 长度
     */
    public static function random($type = Str::ALNUM, $length = 6) {
        switch ($type) {
            case Str::NUM:
                return mt_rand();
            case Str::UNIQUE:
                return md5(uniqid(mt_rand()));
            case Str::SHA1 :
                return sha1(uniqid(mt_rand(), true));
            case Str::UUID:
                $pool = array('8', '9', 'a', 'b');
                return sprintf('%s-%s-4%s-%s%s-%s', static::random('hexdec', 8), static::random('hexdec', 4), static::random('hexdec', 3), $pool[array_rand($pool)], static::random('hexdec', 3), static::random('hexdec', 12));
            case Str::ALPHA:
            case Str::ALNUM:
            case Str::NUMBERIC:
            case Str::NOZERO:
            case Str::DISTINCT:
            case Str::HEXDEC:
                switch ($type) {
                    case Str::ALPHA:
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    default:
                    case Str::ALNUM:
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case Str::NUMBERIC:
                        $pool = '0123456789';
                        break;
                    case Str::NOZERO:
                        $pool = '123456789';
                        break;
                    case Str::NOZERO:
                        $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                        break;
                    case Str::HEXDEC:
                        $pool = '0123456789abcdef';
                        break;
                }
                $str = '';
                for ($i = 0; $i < $length; $i++) {
                    $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
                }
                return $str;
        }
    }

    public static function len($str, $encoding = 'UTF-8') {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, $encoding);
        }
        return strlen($str);
    }

    public static function startsWith($string, $start, $caseSensitive = true) {
        if ($caseSensitive == false) {
            $string = strtolower($string);
        }
        return $start === "" || strrpos($string, $start, -strlen($string)) !== FALSE;
    }

    public static function endsWith($string, $end, $caseSensitive = true) {
        if ($caseSensitive == false) {
            $string = strtolower($string);
        }
        return $end === "" || (($temp = strlen($string) - strlen($end)) >= 0 && strpos($string, $end, $temp) !== FALSE);
    }

    public static function startsWithChar($needle, $haystack) {
        return ($needle[0] === $haystack);
    }

    public static function endsWithChar($needle, $haystack) {
        return (substr($needle, -1) === $haystack);
    }

    /**
     * FormatType
     */
    public static function isJson($str) {
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function isHtml($str) {
        return strlen(strip_tags($str)) < strlen($str);
    }

    public static function replaceArray($search, array $replace, $subject) {
        foreach ($replace as $value) {
            $subject = preg_replace('/' . $search . '/', $value, $subject, 1);
        }
        return $subject;
    }

    /**
     * 
     * @param string $haystack
     * @param string|array $needles
     * @return boolean
     */
    public static function contains($haystack, $needles) {
        if (is_string($needles)) {
            return strpos($haystack, $needles);
        }
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function containsAll($haystack, $needles) {
        if (is_string($needles)) {
            return strpos($haystack, $needles);
        }
        $all_needle = true;
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false && $all_needle) {
                $all_needle = true;
            } else {
                $all_needle = false;
            }
        }
        return $all_needle;
    }

    public static function truncate($value, $limit = 100, $end = '...') {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    public static function substr($string, $start, $length = null) {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    public static function slug($title, $separator = '-') {
        $title = static::ascii($title);
        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
        return trim($title, $separator);
    }

    /**
     * 判断是否是ASCII
     * @param type $string
     * @return type
     */
    public static function isAscii($string) {
        return !preg_match('/[^\x00-\x7F]/S', $string);
    }

    function stripImageTags($str) {
        return preg_replace(array('#<img[\s/]+.*?src\s*=\s*["\'](.+?)["\'].*?\>#', '#<img[\s/]+.*?src\s*=\s*(.+?).*?\>#'), '\\1', $str);
    }

    /**
     * 验证手机号码
     */
    public static function isMobile($str) {
        if (empty($str)) {
            return false;
        }

        return preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#', $str);
    }

    /**
     * 验证固定电话
     */
    public static function isTel($str) {
        if (empty($str)) {
            return true;
        }
        return preg_match('/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/', trim($str));
    }

    /**
     * 验证qq号码
     */
    public static function isQQ($str) {
        if (empty($str)) {
            return false;
        }

        return preg_match('/^[1-9]\d{4,12}$/', trim($str));
    }

    /**
     * 验证邮政编码
     */
    public static function isZipCode($str) {
        if (empty($str)) {
            return true;
        }

        return preg_match('/^[1-9]\d{5}$/', trim($str));
    }

    /**
     * 验证ip
     */
    public static function isIP($value) {
        return filter_var($value, \FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证身份证(中国)
     */
    public static function idCard($str) {
        $str = trim($str);
        if (empty($str)) {
            return false;
        }

        if (preg_match("/^([0-9]{15}|[0-9]{17}[0-9a-z])$/i", $str)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证网址
     */
    public static function isURL($str) {
        if (empty($str)) {
            return false;
        }

        return preg_match('#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str) ? false : true;
    }

}
