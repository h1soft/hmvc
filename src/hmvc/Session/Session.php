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

namespace hmvc\Session;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

/**
 * Description of Session
 *
 * @author allen <allen@w4u.cn>
 */
class Session extends SymfonySession {

    /**
     * 
     * @param string $type (notice|error|success|warning|info)
     * @param string $message
     */
    public function addFlash($type, $message) {
        $this->getFlashBag()->add($type, $message);
    }

    /**
     * 
     * @param string $type
     * @param array $default
     * @return string
     */
    public function getFlash($type, array $default = array()) {
        return $this->getFlashBag()->get($type, $default);
    }

    /**
     * 
     * @param string $type
     * @return array
     */
    public function hasFlash($type) {
        return $this->getFlashBag()->has($type);
    }

}
