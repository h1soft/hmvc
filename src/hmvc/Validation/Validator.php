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
  $validator = Validator::make(Request::data());
  Validator::setDefaultMessage(array(
  'required' => "{label}必须填写"
  ));
  $validator->separator = '||';
  $validator->addRule('name', 'required||match{(allen|hello)}', array(
  'required' => '用户名不能为空',
  'match' => '用户名不合法'
  ));
  if ($validator->validate()) {

  }
 */

/**
 * Description of Validator
 *
 * @author Administrator
 */
class Validator {

    protected $fields;
    protected $errors = array();
    protected $labels = array();
    protected static $messages = array(
        'required' => '{label}不能为空',
        'int' => '{label}必须为数字',
        'float' => '{label}不是float类型',
        'bool' => '{label}必须为0/1 或者 yes/no',
        'ip' => '{label}不是一个合法的IP地址',
        'url' => '{label}不是一个合法的URL',
        'email' => '{label}不是一个合法的Email地址',
        'len' => '{label}长度不合法',
        'range' => '{label}不在范围内',
        'same_as' => '{label} 不相同',
        'match' => '{label} 是无效的',
    );
    protected $context;
    public $separator = '|';
    protected static $defaultRules = array(
        'required' => 'validateRequired',
        'len' => 'validateLength',
        'min' => 'validateMin',
        'max' => 'validateMax',
        'date' => 'validateDate',
        'int' => 'validateInt',
        'bool' => 'validateBool',
        'range' => 'validateRange',
        'same_as' => 'validateSame',
        'email' => 'validateEmail',
        'match' => 'validateMatch',
    );
    protected $rules = array();

    protected function __construct($fields) {
        $this->fields = $fields;
    }

    public static function make($fields, $rules = array()) {
        $validator = new static($fields);
        $validator->initialize();
        $validator->rules = array_merge($validator->rules, $rules);
        return $validator;
    }

    public function initialize() {
        
    }

    public function validate() {
        foreach ($this->rules as $fieldName => $ruleText) {
            $rules = explode($this->separator, $ruleText);
            $this->context = array();
            $this->context['required'] = false;
            foreach ($rules as $rule) {
                preg_match('/^\w+/', $rule, $matched);
                if (!isset($matched[0])) {
                    continue; //error                    
                }
                $command = $matched[0];
                $this->context['params'] = array();
                $rule_string = preg_replace("/^{$command}/", "", $rule);
                preg_match_all('/([\w$_^(|)]+)/', $rule_string, $matched);
                if (isset($matched[0])) {
                    $this->context['params'] = $matched[0];
                }
                if (isset(static::$defaultRules[$command]) && method_exists($this, static::$defaultRules[$command])) {
                    call_user_func_array(array($this, static::$defaultRules[$command]), array($fieldName));
                    if (isset($this->context['return'])) {
                        continue;
                    }
                } else {
                    throw new \Exception("The Rule {$command} does not support");
                }
            }
        }
        return empty($this->errors);
    }

    public function addRule($name, $rules = '', $labelText = '', $messages = array()) {
        if (is_string($labelText)) {
            $this->labels[$name] = $labelText;
        } else if (is_array($labelText)) {
            static::$messages[$name] = $labelText;
        } else {
            static::$messages[$name] = $messages;
        }
        if (empty($rules)) {
            throw new Exception(__METHOD__ . 'rules is Invalid');
        }
        $this->rules[$name] = $rules;
    }

    public static function setDefaultMessage($messages, $message = '') {
        if (is_array($messages)) {
            static::$messages = array_merge(static::$messages, $messages);
        } else {
            static::$messages[$messages] = $message;
        }
    }

    public function getMessage($fieldName, $ruleName) {
        if (isset(static::$messages[$fieldName][$ruleName])) {
            return static::$messages[$fieldName][$ruleName];
        }
        if (!isset(static::$messages[$ruleName])) {
            return '';
        }
        $label = $this->getLabelText($fieldName);
        return preg_replace("/\{label\}/", $label, static::$messages[$ruleName]);
    }

    public function getLabelText($name) {
        return isset($this->labels[$name]) ? $this->labels[$name] : NULL;
    }

    public function setError($fieldName, $message) {
        if (isset($this->errors[$fieldName])) {
            $this->errors[$fieldName] = $message;
        } else {
            $this->errors[$fieldName] = $message;
        }
        $this->context['return'] = true;
    }

    public function addError($fieldName, $message) {
        if (isset($this->errors[$fieldName])) {
            $this->errors[$fieldName] = $message;
        } else {
            $this->errors[$fieldName] = $message;
        }
        return $this;
    }

    /**
     * 
     * @return array all errors
     */
    public function errors() {
        return $this->errors;
    }

    /*
     * ----------------------------------
     * Validator Func
     * ----------------------------------
     */

    protected function validateRequired($fieldName) {
        if (!isset($this->fields[$fieldName]) || $this->fields[$fieldName] == "") {
            $this->setError($fieldName, $this->getMessage($fieldName, 'required'));
        } else {
            $this->context['required'] = true;
        }
    }

    protected function validateInt($fieldName) {
        if ($this->context['required'] == false) {
            return true;
        }
        if (filter_var($this->fields[$fieldName], FILTER_VALIDATE_INT) === FALSE) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'int'));
        }
    }

    protected function validateBool($fieldName) {
        if ($this->context['required'] == false && strlen($this->fields[$fieldName]) == 0) {
            return true;
        }
        if (filter_var($this->fields[$fieldName], FILTER_VALIDATE_BOOLEAN) === FALSE) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'bool'));
        }
    }

    protected function validateEmail($fieldName) {
        if ($this->context['required'] == false) {
            return true;
        }
        if (filter_var($this->fields[$fieldName], FILTER_VALIDATE_EMAIL) === FALSE) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'email'));
        }
    }

    protected function validateLength($fieldName) {

        if ($this->context['required'] == false) {
            return true;
        }
        $params = $this->context['params'];
        $start = \hmvc\Helpers\Arr::get($params, 0, -1);
        $end = \hmvc\Helpers\Arr::get($params, 1, -1);
        $len = \hmvc\Helpers\Str::len($this->fields[$fieldName]);
        if ($len != $start) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'len'));
            return false;
        } else if ($len < $start) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'len'));
            return false;
        } else if ($len > $end) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'len'));
            return false;
        }
    }

    protected function validateRange($fieldName) {

        if ($this->context['required'] == false) {
            return true;
        }
        $params = $this->context['params'];
        $start = \hmvc\Helpers\Arr::get($params, 0, -1);
        $end = \hmvc\Helpers\Arr::get($params, 1, false);
        $value = $this->fields[$fieldName];
        if ($end === false && $value != $start) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'range'));
            return false;
        } else if ($value < $start) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'range'));
            return false;
        } else if ($value > $end) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'range'));
            return false;
        }
    }

    protected function validateSame($fieldName) {

        if ($this->context['required'] == false) {
            return true;
        }
        $params = $this->context['params'];
        $two = \hmvc\Helpers\Arr::get($params, 0, false);
        $value = $this->fields[$fieldName];
        $two = isset($this->fields[$two]) ? $this->fields[$two] : '';
        if ($value != $two) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'same_as'));
            return false;
        }
    }

    public function validateMatch($fieldName) {
        if ($this->context['required'] == false) {
            return true;
        }
        $params = $this->context['params'];
        $regex = \hmvc\Helpers\Arr::get($params, 0, false);
        if (!preg_match("#{$regex}#", $this->fields[$fieldName])) {
            $this->setError($fieldName, $this->getMessage($fieldName, 'match'));
        }
    }

}
