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

/**
 * Description of Route
 *
 * @author allen <allen@w4u.cn>
 */
class Route {

    public static function get($pattern, $callable) {
        return app()->router->AddRoute($pattern, $callable)->via('GET');
    }

    public static function post($pattern, $callable) {
        return app()->router->AddRoute($pattern, $callable)->via('POST');
    }

    public static function delete($pattern, $callable) {
        return app()->router->AddRoute($pattern, $callable)->via('DELETE');
    }

    public static function put($pattern, $callable) {
        return app()->router->AddRoute($pattern, $callable)->via('PUT');
    }

    public static function resources($pattern, $resource) {
        if (!is_string($resource)) {
            throw new Exception("Route::resources 第二个参数必须是string");
        }
        $router = app()->router;
        $router->AddRoute($pattern, "$resource:index")->via('GET');
        $router->AddRoute("$pattern/:id", "$resource:show")->via('GET');
        $router->AddRoute("$pattern", "$resource:save")->via('POST');
        $router->AddRoute("$pattern/:id", "$resource:update")->via('PUT');
        $router->AddRoute("$pattern/:id", "$resource:delete")->via('DELETE');
    }

    public static function scope($prefix, $params, $mvc = false) {
        if (!$mvc) {
            app()->router->hmvc($prefix, $params);
        } else {
            app()->router->mvc($prefix, $params);
        }
    }

}
