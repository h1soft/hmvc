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

namespace hmvc\Core;

use hmvc\Http\Response;

/**
 * Description of StackTrace
 *
 * @author Administrator
 */
class StackTrace {

    public static function systemError($code, $message, $file, $line) {
        $showTrace = self::debugBacktrace();
        self::showError('system', $message, $showTrace);
    }

    /**
     * 代码执行过程回溯信息
     *
     * @static
     * @access public
     */
    public static function debugBacktrace() {

        $show = array();
        $debugBacktrace = debug_backtrace();
        ksort($debugBacktrace);
        array_shift($debugBacktrace);
        array_shift($debugBacktrace);
        foreach ($debugBacktrace as $k => $entry) {
            if (!isset($entry['file'])) {
                try {
                    if (isset($entry['class'])) {
                        $reflection = new \ReflectionMethod($entry['class'], $entry['function']);
                    } else {
                        $reflection = new \ReflectionFunction($entry['function']);
                    }
                    $entry['file'] = $reflection->getFileName();
                    $entry['line'] = $reflection->getStartLine();
                } catch (Exception $e) {
                    continue;
                }
            }

            $entry['file'] = str_replace(base_path(), '', $entry['file']);
            $func = isset($entry['class']) ? $entry['class'] : '';
            $func .= isset($entry['type']) ? $entry['type'] : '';

            if (isset($entry['function']) && $entry['function'] == 'hmvcError') {
                $func .= 'break  <a href="#" style="color:red;" onclick="showCode();">@Source Code</a><script>function showCode(){document.getElementById("sourceCode").style.display = document.getElementById("sourceCode").style.display=="none" ? "" : "none";}</script><div id="sourceCode" style="display:none">'
                        . self::_showSource($entry['file'], $entry['line'], 2) . '</div>';
            } else {
                $func .= $entry['function'];
            }


            $entry['function'] = $func;
            $entry['line'] = sprintf('%05d', $entry['line']);
            $show[] = $entry;
        }
        return $show;
    }

    /**
     * 异常处理
     *
     * @static
     * @access public
     * @param mixed $exception
     */
    public static function exceptionError($exception) {
        if ($exception instanceof DatabaseException) {
            $type = 'db';
        } else {
            $type = 'system';
        }
        if ($type == 'db') {
            $errorMsg = '(' . $exception->getCode() . ') ';
            $errorMsg .= self::sqlClear($exception->getMessage(), $exception->getDbConfig());
            if ($exception->getSql()) {
                $errorMsg .= '<div class="dbclass">';
                $errorMsg .= self::sqlClear($exception->getSql(), $exception->getDbConfig());
                $errorMsg .= '</div>';
            }
        } else {
            $errorMsg = $exception->getMessage();
        }
        $trace = $exception->getTrace();
        $showcode = '<a href="#" style="color:red;" onclick="showCode();">@Source Code</a><script>function showCode(){document.getElementById("sourceCode").style.display = document.getElementById("sourceCode").style.display=="none" ? "" : "none";}'
                . '</script><div id="sourceCode" style="display:none">' . self::_showSource($exception->getFile(), $exception->getLine(), 2) . '</div>';
        array_unshift($trace, array('file' => $exception->getFile(), 'line' => $exception->getLine(), 'function' => 'break ' . $showcode));
        $phpMsg = array();
        foreach ($trace as $error) {
            if (!empty($error['function'])) {
                $fun = '';
                if (!empty($error['class'])) {
                    $fun .= $error['class'] . $error['type'];
                }
                $fun .= $error['function'];
                $error['function'] = $fun;
            }
            if (!isset($error['line'])) {
                continue;
            }
            $phpMsg[] = array('file' => str_replace(array(base_path(), ''), array('', '/'), $error['file']), 'line' => $error['line'], 'function' => $error['function']);
        }
        self::showError($type, $errorMsg, $phpMsg);
        exit();
    }

    private static function _showSource($file, $lineNumber, $padding = 7) {
        if (!$file OR ! is_readable($file)) {
            return false;
        }

        $file = fopen($file, 'r');
        $line = 0;

        # Set the reading range
        $range = array(
            'start' => $lineNumber - $padding,
            'end' => $lineNumber + $padding
        );

        # set the zero-padding amount for line numbers
        $format = '% ' . strlen($range['end']) . 'd';

        $source = '';
        while (( $row = fgets($file) ) !== false) {
            # increment the line number
            if (++$line > $range['end']) {
                break;
            }

            if ($line >= $range['start']) {
                # make the row safe for output
                $row = htmlspecialchars($row, ENT_NOQUOTES, 'UTF-8');

                # trim whitespace and sanitize the row
                $row = '<span>' . sprintf($format, $line) . '</span> ' . $row;

                if ($line === $lineNumber) {
                    # apply highlighting to this row
                    $row = '<div class="highlight">' . $row . '</div>';
                } else {
                    $row = '<div>' . $row . '</div>';
                }

                # add to the captured source
                $source .= $row;
            }
        }

        # close the file
        fclose($file);

        return $source;
    }

    /**
     * 清除文本部分字符
     *
     * @param string $message
     */
    public static function clear($message) {
        return str_replace(array("t", "r", "n"), " ", $message);
    }

    /**
     * sql语句字符清理
     *
     * @static
     * @access public
     * @param string $message
     * @param string $dbConfig
     */
    public static function sqlClear($message, $dbConfig) {
        $message = self::clear($message);
        if (!(defined('SITE_DEBUG') && SITE_DEBUG)) {
            $message = str_replace($dbConfig['database'], '***', $message);
            //$message = str_replace($dbConfig['prefix'], '***', $message);
            $message = str_replace(C('DB_PREFIX'), '***', $message);
        }
        $message = htmlspecialchars($message);
        return $message;
    }

    /**
     * 显示错误
     *
     * @static
     * @access public
     * @param string $type 错误类型 db,system
     * @param string $errorMsg
     * @param string $phpMsg
     */
    public static function showError($type, $errorMsg, $phpMsg = '') {
        $errorMsg = str_replace(base_path(), '', $errorMsg);
        ob_end_clean();
        $host = $_SERVER['HTTP_HOST'];
        $title = $type == 'db' ? 'Database' : 'Runtime';
        ob_start();
        echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>$host - $title Error</title>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
 <style type="text/css">
 body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
 #container {margin: 10px;}
 #message {width: 1024px; color: black;}
 .red {color: red;}
 a:link {font: 9pt/11pt verdana, arial, sans-serif; color: red;}
 a:visited {font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e;}
 h1 {color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
 .bg1 {background-color: #FFFFCC;}
 .rowTitle {background-color: #EEEEEE;}
 .table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
 .info {
  background: none repeat scroll 0 0 #F3F3F3;
  border: 0px solid #aaaaaa;
  border-radius: 10px 10px 10px 10px;
  color: #000000;
  font-size: 11pt;
  line-height: 160%;
  margin-bottom: 1em;
  padding: 1em;
 }
 .help {
  background: #F3F3F3;
  border-radius: 10px 10px 10px 10px;
  font: 12px verdana, arial, sans-serif;
  text-align: center;
  line-height: 160%;
  padding: 1em;
 }
 .sql {
  background: none repeat scroll 0 0 #FFFFCC;
  border: 1px solid #aaaaaa;
  color: #000000;
  font: arial, sans-serif;
  font-size: 9pt;
  line-height: 160%;
  margin-top: 1em;
  padding: 4px;
 }
.highlight{
background   : rgb(226, 135, 135);
}
 </style>
</head>
<body>
<div id="container">
<h1>$title Error</h1>
<div class='info'>$errorMsg</div>
EOT;
        if (!empty($phpMsg)) {
            echo '<div class="info">';
            echo '<p><strong>PHP Debug</strong></p>';
            echo '<table cellpadding="5" cellspacing="1" width="100%" class="table"><tbody>';
            if (is_array($phpMsg)) {
                echo '<tr class="rowTitle"><td>No.</td><td>File</td><td>Line</td><td>Code</td></tr>';
                foreach ($phpMsg as $k => $msg) {
                    $k++;
                    echo '<tr class="bg1">';
                    echo '<td>' . $k . '</td>';
                    echo '<td>' . $msg['file'] . '</td>';
                    echo '<td>' . $msg['line'] . '</td>';
                    echo '<td>' . $msg['function'] . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td><ul>' . $phpMsg . '</ul></td></tr>';
            }
            echo '</tbody></table></div>';
        }
        echo <<<EOT
</div>
</body>
</html>
EOT;

        $response = new Response(
                ob_get_clean(), Response::HTTP_OK, array('content-type' => 'text/html')
        );
        $response->setCharset('UTF-8');
        $response->send();
        exit();
    }

}
