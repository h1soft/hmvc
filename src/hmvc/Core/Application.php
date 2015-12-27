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

use hmvc\Container\Container;
use hmvc\Events\Dispatcher;
use hmvc\Core\Http;

require 'helpers.php';

/**
 * Description of Application
 *
 * @author Administrator
 */
class Application extends Container {

    const VERSION = '0.0.1 (ALPHA)';
    const HTTP_KERNEL = 'http';
    const CONSOLE_KERNEL = 'console';

    /**
     *
     * @var type 根目录
     */
    protected $basePath;

    /**
     * @var string 代码目录
     */
    protected $sourcePath = 'app';

    /**
     *
     * @var string config
     */
    protected $configPath = 'config';

    /**
     *
     * @var string 存储目录
     */
    protected $storagePath = 'storage';

    /**
     *
     * @var string assets
     */
    protected $assetsPath = 'assets';

    /**
     *
     * @var string Resources
     */
    protected $resourcesPath = 'resources';

    /**
     * @var string 环境类型
     */
    protected $environment = 'production';

    public function __construct($basePath = null) {
        set_error_handler("hmvcError");
        set_exception_handler("hmvcExceptionHandler");
        $this->basePath = $basePath;
        static::setInstance($this);
        $this->set('app', $this);

        //event
        $this->registerBaseEvent();

        //classloader
        $loader = new \hmvc\Core\ClassLoader();
        //default app
        $loader->addPrefix('App', $this->basePath . '/app');
        $loader->register();
        $this->singleton('loader', $loader);
    }

    private function registerBaseEvent() {
        $this->set('events', new Dispatcher($this));
    }

    /**
     * HMVC Version
     * @return string
     */
    public function version() {
        return static::VERSION;
    }

    /**
     * App BasePath
     * @return string
     */
    public function basePath() {
        return $this->basePath;
    }

    public function sourcePath() {
        return $this->sourcePath;
    }

    public function configPath() {
        return $this->configPath;
    }

    public function storagePath() {
        return $this->storagePath;
    }

    public function assetsPath() {
        return $this->assetsPath;
    }

    public function resourcesPath() {
        return $this->resourcesPath;
    }

    public function setBasePath($basePath) {
        $this->basePath = $basePath;
        return $this;
    }

    public function setSourcePath($sourcePath) {
        $this->sourcePath = $sourcePath;
        return $this;
    }

    public function setStoragePath($storagePath) {
        $this->storagePath = $storagePath;
        return $this;
    }

    public function setAssetsPath($assetsPath) {
        $this->assetsPath = $assetsPath;
        return $this;
    }

    public function setResourcesPath($resourcesPath) {
        $this->resourcesPath = $resourcesPath;
        return $this;
    }

    public function setEnvironment($environment) {
        $this->environment = $environment;
        return $this;
    }

    /**
     * 
     * @param int $type HTTP_KERNEL
     * @return hmvc\Core\Http
     */
    public function handle($type = Application::HTTP_KERNEL) {
        $kernel = null;
        if ($type == Application::HTTP_KERNEL) {
            $kernel = new Http($this);
        } else {
            $kernel = new Console($this);
        }
        $this->singleton('kernel', $kernel);
        return $kernel;
    }

    public function getKernel() {
        return $this->kernel;
    }

    public function isConsole() {
        return php_sapi_name() == 'cli';
    }

}