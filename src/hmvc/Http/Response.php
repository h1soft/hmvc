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

namespace hmvc\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Description of Response
 *
 * @author Administrator
 */
class Response extends SymfonyResponse {

    /**
     * 
     * @param type $content
     * @param type $status
     * @param type $headers
     * @return \hmvc\Http\Response
     */
    public static function make($content = '', $status = 200, $headers = array()) {
        return new Response($content, $status, $headers);
    }

    /**
     * 
     * @param array|string $content
     * @param int $status
     * @param array $headers
     * @return \hmvc\Http\JsonResponse
     */
    public static function json($content = '', $status = 200, $headers = array()) {
        return JsonResponse::make($content, $status, $headers);
    }

}
