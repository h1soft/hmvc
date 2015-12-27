<<<<<<< HEAD
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

namespace hmvc\Log;

/**
 * Description of Logger
 *
 * @author Administrator
 */
class Logger {
    
}
=======
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

namespace hmvc\Log;

/**
 * Description of Logger
 *
 * @author Administrator
 */
class Logger {

    const ERROR = 'ERROR';  // 一般错误: 一般性错误
    const WARNING = 'WARNING';  // 警告性错误: 需要发出警告的错误
    const NOTICE = 'NOTICE';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO = 'INFO';  // 信息: 程序输出信息
    const DEBUG = 'DEBUG';  // 调试: 调试信息
    const SQL = 'SQL';

    private $driver = 'file';
    private static $logInstance;
    private static $logCache = array();

    /**
     * 直接写入
     * @param type $message
     * @param type $type
     */
    public static function write($message, $type = self::ERROR) {
        $log = self::getLog();
        $log->write($message, $type);
    }

    public static function getLog() {
        $model = self::model();
        switch ($model->driver) {
            case 'file':
                if (!isset(self::$logInstance)) {
                    self::$logInstance = new Driver\File();
                }
                break;

            default:
                throw new \Exception("Log驱动不存在");
        }
        return self::$logInstance;
    }

}
>>>>>>> origin/master
