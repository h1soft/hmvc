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

namespace hmvc\Validation;

/**
 * Description of Validator
 *
 * @author Administrator
 */
abstract class Validator {

    public static $regexes = Array(
        'date' => "^[0-9]{4}[-/][0-9]{1,2}[-/][0-9]{1,2}\$",
        'amount' => "^[-]?[0-9]+\$",
        'number' => "^[-]?[0-9,]+\$",
        'alfanum' => "^[0-9a-zA-Z ,.-_\\s\?\!]+\$",
        'not_empty' => "[a-z0-9A-Z]+",
        'words' => "^[A-Za-z]+[A-Za-z \\s]*\$",
        'phone' => "^[0-9]{10,11}\$",
        'zipcode' => "^[1-9]{1}[0-9]{3}\$",
        'plate' => "^([0-9a-zA-Z]{2}[-]){2}[0-9a-zA-Z]{2}\$",
        'price' => "^[0-9.,]*(([.,][-])|([.,][0-9]{2}))?\$",
        '2digitopt' => "^\d+(\,\d{2})?\$",
        '2digitforce' => "^\d+\,\d\d\$",
        'anything' => "^[\d\D]{1,}\$"
    );
    private $fields;
    private $messages;
    private $errors;
    public static $rules = array();

    private function __construct($fields) {
        $this->fields = $fields;
        $this->errors = array();
    }

    public static function make($fields, $rules = array()) {
        static::$rules = array_merge(static::$rules, $rules);
        $validator = new static($fields);
        $validator->initialize();
        return $validator;
    }

    public function initialize() {
        
    }

    public function validate() {
        
    }

    public function addRule($name, $ruleName, $message = null) {
        if (!is_null($message)) {
            $this->messages[$ruleName] = $message;
        }
    }
    
    public function addCustomRule() {
        
    }

    //https://github.com/fuelphp/validation
    //https://github.com/vlucas/valitron/blob/master/src/Valitron/Validator.php
    
}
