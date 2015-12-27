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

namespace hmvc\Log\Driver;

use hmvc\Log;

/**
 * Description of File
 *
 * @author allen <i@w4u.cn>
 */
class File {

    protected $logPath;
    protected $logTimeFormat = 'Y-m-d H:i:s';

    public function __construct() {
        $this->logPath = rootPath() . 'var/logs/';
    }

    public function write($message, $type = Log::ERROR) {
        $log_file = '';
        $now = date($this->logTimeFormat);
        if (empty($log_file)) {
            $log_file = $this->logPath . date('Y_m_d') . '.log';
        }
        // 自动创建日志目录
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($log_file) && floor(config('logs.log_file_size', 102400)) <= filesize($log_file)) {
            rename($log_file, dirname($log_file) . '/' . time() . '-' . basename($log_file));
        }
        error_log("[{$now}] {$type} " . $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['REQUEST_URI'] . "  {$message}\r\n", 3, $log_file);
    }

}
