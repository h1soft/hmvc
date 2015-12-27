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

namespace hmvc\Database\Driver;

use PDO;
use hmvc\Database\Driver;

/**
 * Description of Mysqli
 *
 * @author Administrator
 */
class Pgsql extends PDO implements Driver {

    public function __construct($params, $options = array()) {
        $dbname = array_get($params, 'dbname', 'test');
        $host = array_get($params, 'host', 'root');
        $port = array_get($params, 'port', '3306');
        $username = array_get($params, 'username', 'root');
        $passwd = array_get($params, 'password', '');
        $charset = array_get($params, 'charset', 'utf8');
        $dsn = 'pgsql:dbname=' . $dbname . ';host=' . $host . ';port=' . $port;
        parent::__construct($dsn, $username, $passwd, $options);
        if (!empty($charset)) {
            $this->exec('SET NAMES ' . $this->quote($charset));
        }
    }

    public function getName() {
        return 'pdo_mysql';
    }

    public function begin() {
        $this->beginTransaction();
    }

    public function sanitizer() {
        return '"';
    }

    public function quoteColumnName($name) {
        return '"' . $name . '"';
    }

    public function quoteTableName($name) {
        return '"' . $name . '"';
    }

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

namespace hmvc\Database\Driver;

use PDO;
use hmvc\Database\Driver;

/**
 * Description of Mysqli
 *
 * @author allen <allen@w4u.cn>
 */
class Pgsql extends PDO implements Driver {

    const PDO_STATEMENT_WRAPPER_CLASS = '\\hmvc\\Database\\Statement';

    public function __construct($params, $options = array()) {
        $dbname = array_get($params, 'dbname', 'test');
        $host = array_get($params, 'host', 'root');
        $port = array_get($params, 'port', '3306');
        $username = array_get($params, 'username', 'root');
        $passwd = array_get($params, 'password', '');
        $charset = array_get($params, 'charset', 'utf8');
        $dsn = 'pgsql:dbname=' . $dbname . ';host=' . $host . ';port=' . $port;
        parent::__construct($dsn, $username, $passwd, $options);
        if (!empty($charset)) {
            $this->exec('SET NAMES ' . $this->quote($charset));
        }
    }

    public function getName() {
        return 'pdo_pgsql';
    }

    public function begin() {
        $this->beginTransaction();
    }

    public function sanitizer() {
        return '"';
    }

    public function quoteColumnName($name) {
        return '"' . $name . '"';
    }

    public function quoteTableName($name) {
        return '"' . $name . '"';
    }

}
>>>>>>> origin/master
