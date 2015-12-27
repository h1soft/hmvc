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

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Description of Request
 *
 * @author Administrator
 */
class Request extends SymfonyRequest {

    const METHOD_ANY = 'ANY';

    protected static $instance;
    protected $segments;
    protected $params;

    /**
     * 
     * @return \hmvc\Http\Request
     */
    public static function classic() {
        if (is_null(self::$instance)) {
            self::$instance = new Request();
            //允许_method覆盖
            static::enableHttpMethodParameterOverride();
            $symfonyRequest = static::createFromGlobals();
            $content = $symfonyRequest->content;
            static::$instance = static::$instance->duplicate(
                    $symfonyRequest->query->all(), $symfonyRequest->request->all(), $symfonyRequest->attributes->all(), $symfonyRequest->cookies->all(), $symfonyRequest->files->all(), $symfonyRequest->server->all()
            );
            static::$instance->content = $content;
        }
        return self::$instance;
    }

    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null) {
        return parent::duplicate($query, $request, $attributes, $cookies, array_filter((array) $files), $server);
    }

    /**
     * 
     * @return \hmvc\Session\Session
     */
    public function session() {
        if (!$this->hasSession()) {
            $this->setSession(app()->get('session'));
        }
        return $this->session;
    }

    public function getSession() {
        if (!$this->hasSession()) {
            $this->setSession(app()->get('session'));
        }
        return $this->session;
    }

    public function isAjax() {
        return $this->isXmlHttpRequest();
    }

    public function ip() {
        return $this->getClientIp();
    }

    public function ips() {
        return $this->getClientIps();
    }

    public function baseUrl() {
        return rtrim($this->getSchemeAndHttpHost() . $this->getBaseUrl(), '/');
    }

    public function url() {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    public function fullUrl() {
        $query = $this->getQueryString();
        return $query ? $this->url() . '?' . $query : $this->url();
    }

    public function method() {
        return $this->getMethod();
    }

    public function segments() {
        $segments = explode('/', $this->pathinfo());
        return array_values(array_filter($segments, function ($v) {
                    return $v != '';
                }));
    }

    public function pathinfo() {
        $pathinfo = trim($this->getPathInfo(), '/');
        return $pathinfo == '' ? '/' : $pathinfo;
    }

    public function _setParams($params) {
        if (is_array($params)) {
            $this->params = $params;
        }
    }

    public function param($name) {
        return isset($this->params[$name]) ? $this->params[$name] : NULL;
    }

    public static function data() {
        return static::classic()->method() == 'GET' ? static::classic()->query->all() : static::classic()->request->all();
    }

    public function isJson() {
        return $this->getContentType() == 'json';
    }

    public function isXml() {
        return $this->getContentType() == 'xml';
    }

}
