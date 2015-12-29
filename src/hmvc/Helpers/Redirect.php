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

use hmvc\Http\RedirectResponse;

/**
 * Package hmvc\Helpers  Redirect
 *
 * @author allen <allen@w4u.cn>
 */
class Redirect {

    public static function to($url, $status = 302, $headers = array()) {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * 
     * @param string $url
     * @param array|string $data
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public static function action($url, $data = '', $status = 302, $headers = array()) {
        $segments = explode('/', $url);
        switch (count($segments)) {
            case 1:
                $url = base_url(app()->get('hmvcDispatch')->getPathModule()) . '/' . $url;
                break;
            case 2:
                $url = base_url(app()->get('hmvcDispatch')->getPathModule()) . '/' . $url;
                break;
            case 3:
                $url = base_url(app()->get('hmvcDispatch')->getPathPrefix()) . '/' . $url;
                break;
            default:
                $url = base_url();
                break;
        }
        $query = '';
        if (!empty($data)) {
            $query = is_array($data) ? '?' . http_build_query($data) : '/' . $data;
        }
        return new RedirectResponse(strtolower($url) . $query, $status, $headers);
    }

}
