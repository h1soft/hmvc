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

namespace hmvc\Component;

/**
 * Github https://github.com/devster/ubench
 */

/**
 * Description of Benchmark
 *
 * @author allen <allen@w4u.cn>
 */
class Benchmark {
    protected $start_time;
    protected $end_time;
    protected $memory_usage;
    /**
     * Sets start microtime
     *
     * @return void
     */
    public function start()
    {
        $this->start_time = microtime(true);
    }
    /**
     * Sets end microtime
     *
     * @return void
     */
    public function end()
    {
        $this->end_time = microtime(true);
        $this->memory_usage = memory_get_usage(true);
    }
    /**
     * Returns the elapsed time, readable or not
     *
     * @param  boolean $readable Whether the result must be human readable
     * @param  string  $format   The format to display (printf format)
     * @return string|float
     */
    public function getTime($raw = false, $format = null)
    {
        $elapsed = $this->end_time - $this->start_time;
        return $raw ? $elapsed : self::readableElapsedTime($elapsed, $format);
    }
    /**
     * Returns the memory usage at the end checkpoint
     *
     * @param  boolean $readable Whether the result must be human readable
     * @param  string  $format   The format to display (printf format)
     * @return string|float
     */
    public function getMemoryUsage($raw = false, $format = null)
    {
        return $raw ? $this->memory_usage : self::readableSize($this->memory_usage, $format);
    }
    /**
     * Returns the memory peak, readable or not
     *
     * @param  boolean $readable Whether the result must be human readable
     * @param  string  $format   The format to display (printf format)
     * @return string|float
     */
    public function getMemoryPeak($raw = false, $format = null)
    {
        $memory = memory_get_peak_usage(true);
        return $raw ? $memory : self::readableSize($memory, $format);
    }
    /**
     * Wraps a callable with start() and end() calls
     *
     * Additional arguments passed to this method will be passed to
     * the callable.
     *
     * @param callable $callable
     * @return mixed
     */
    public function run(callable $callable)
    {
        $arguments = func_get_args();
        array_shift($arguments);
        $this->start();
        $result = call_user_func_array($callable, $arguments);
        $this->end();
        return $result;
    }
    /**
     * Returns a human readable memory size
     *
     * @param   int    $size
     * @param   string $format   The format to display (printf format)
     * @param   int    $round
     * @return  string
     */
    public static function readableSize($size, $format = null, $round = 3)
    {
        $mod = 1024;
        if (is_null($format)) {
            $format = '%.2f%s';
        }
        $units = explode(' ','B Kb Mb Gb Tb');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        if (0 === $i) {
            $format = preg_replace('/(%.[\d]+f)/', '%d', $format);
        }
        return sprintf($format, round($size, $round), $units[$i]);
    }
    /**
     * Returns a human readable elapsed time
     *
     * @param  float $microtime
     * @param  string  $format   The format to display (printf format)
     * @return string
     */
    public static function readableElapsedTime($microtime, $format = null, $round = 3)
    {
        if (is_null($format)) {
            $format = '%.3f%s';
        }
        if ($microtime >= 1) {
            $unit = 's';
            $time = round($microtime, $round);
        } else {
            $unit = 'ms';
            $time = round($microtime*1000);
            $format = preg_replace('/(%.[\d]+f)/', '%d', $format);
        }
        return sprintf($format, $time, $unit);
    }
}
