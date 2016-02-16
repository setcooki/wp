<?php

/**
 * get values from objects/array by key/path
 *
 * @param object|array $object expects object to get value from
 * @param null|string $key expects a key to look up in object or null to return the object
 * @param null|mixed $default expects default return value
 * @return mixed
 * @throws Exception
 */
function setcooki_object_get($object, $key = null, $default = null)
{
    if(is_object($object))
    {
        $object = setcooki_object_to_array($object);
        $o = true;
    }else{
        $object = (array)$object;
        $o = false;
    }
    if($key !== null)
    {
        if(array_key_exists($key, $object))
        {
            return ($o) ? setcooki_array_to_object($object[$key]) : $object[$key];
        }
        foreach(explode('.', trim($key, '.')) as $k => $v)
        {
            if(!is_array($object) || !array_key_exists($v, $object))
            {
                return setcooki_default($default);
            }
            $object = $object[$v];
        }
    }
    return ($o) ? setcooki_array_to_object($object) : $object;
}


/**
 * set values in object/array by key/path
 *
 * @param object|array $object expects object to set
 * @param null|string $key expects a key/path to set value too or null to init object with value in third argument
 * @param null|mixed $value expects the value to set ath key in object
 * @return null|mixed
 */
function setcooki_object_set(&$object, $key = null, $value = null)
{
    if(is_object($object))
    {
        $object = setcooki_object_to_array($object);
        $o = true;
    }else{
        $object = (array)$object;
        $o = false;
    }
    if($key === null)
    {
        $object = $value;
        if($o)
        {
            $object = setcooki_array_to_object($object);
        }
        return $object;
    }
    if(strpos($key, '.') === false)
    {
        $object[$key] = $value;
        if($o)
        {
            $object[$key] = setcooki_array_to_object($object[$key]);
        }
        return $object[$key];
    }
    $keys = explode('.', trim($key, '.'));
    while(count($keys) > 1)
    {
        $key = array_shift($keys);
        if(!isset($object[$key]) || !is_array($object[$key]))
        {
            $object[$key] = array();
        }
        $object =& $object[$key];
    }
    $object[array_shift($keys)] = $value;
    if($o)
    {
        $object = setcooki_array_to_object($object);
    }
    return null;
}


/**
 * unset values of object/array at key/path in second argument or unset the whole object if no key is supplied in second
 * argument
 *
 * @param object|array $object expects object
 * @param null|string $key expects key/path target
 */
function setcooki_object_unset(&$object, $key = null)
{
    if(is_object($object))
    {
        $object = setcooki_object_to_array($object);
        $o = true;
    }else{
        $object = (array)$object;
        $o = false;
    }
    if($key === null)
    {
        $object = array();
    }else{
        if(array_key_exists($key, $object))
        {
            unset($object[$key]);
        }else{
            $keys = explode('.', trim($key, '.'));
            while(count($keys) > 1)
            {
                $key = array_shift($keys);
                if(!isset($object[$key]) or ! is_array($object[$key]))
                {
                    return;
              	}
                $object =& $object[$key];
            }
            unset($object[array_shift($keys)]);
        }
    }
    if($o)
    {
        $object = setcooki_array_to_object($object);
    }
}


/**
 * check if a a value at key/path or key/path exists/isset in first argument object/array. the third arguments tell to
 * also check for a real value at key/path which is not php "empty"
 *
 * @param object|array $object expects object to check
 * @param null|mixed $key expects key/path to check for
 * @param bool $strict expects boolean value for strict mode
 * @return bool
 */
function setcooki_object_isset($object, $key = null, $strict = false)
{
    if(is_object($object))
    {
        $object = setcooki_object_to_array($object);
        $o = true;
    }else{
        $object = (array)$object;
        $o = false;
    }
    if($key === null)
    {
        return (!empty($object)) ? true : false;
    }
    if(array_key_exists($key, $object))
    {
        if((bool)$strict)
        {
            return (setcooki_is_value($object[$key])) ? true : false;
        }else{
            return true;
        }
    }
    foreach(explode('.', trim($key, '.')) as $k => $v)
    {
        if(!is_array($object) || !array_key_exists($v, $object))
        {
            return false;
        }
        $object = $object[$v];
    }
    if($o)
    {
        $object = setcooki_array_to_object($object);
    }
    if((bool)$strict)
    {
        return (setcooki_is_value($object)) ? true : false;
    }else{
        return true;
    }
}


/**
 * convert array to std object
 *
 * @param array|mixed $array the array to convert
 * @return object|mixed
 */
function setcooki_array_to_object($array)
{
    if(is_array($array))
    {
        if(array_keys($array) === range(0, count($array) - 1))
        {
            return (array)array_map(__FUNCTION__, $array);
        }else{
            return (object)array_map(__FUNCTION__, $array);
        }
    }else{
        return $array;
    }
}


/**
 * convert std object to array
 *
 * @param object|mixed $object expects object to convert
 * @return array|mixed
 */
function setcooki_object_to_array($object)
{
    if(is_object($object))
    {
        $value = get_object_vars($object);
    }
    if(is_array($object))
    {
   	    return array_map(__FUNCTION__, $object);
    }else{
   		return $object;
   	}
}


/**
 * check a value for being a not empty/null/false or string '' value identifying only value which are considered to be
 * valid values
 *
 * @param null|mixed $value
 * @return bool
 */
function setcooki_is_value($value = null)
{
    if(is_null($value))
    {
        return false;
    }
    if(is_bool($value) && $value === false)
    {
        return false;
    }
    if(is_array($value) && empty($value))
    {
        return false;
    }
    if(is_string($value) && $value === '')
    {
        return false;
    }
    return true;
}


/**
 * default value function that will take a mixed value as argument and executes it according to values data type which
 * can be a php callback, exception, exit command or default return string
 *
 * @param mixed $value expects the value to execute
 * @return mixed
 * @throws Exception
 */
function setcooki_default($value)
{
    if(is_callable($value) || (is_string($value) && function_exists($value)))
    {
        return call_user_func($value);
    }else if($value instanceof Exception) {
        throw $value;
    }else if($value === 'exit'){
        exit(0);
    }
    return $value;
}


/**
 * pass name => value pairs of array passed in first argument to class instance that implements public $options property
 * in second argument
 *
 * @param array $options expects option array
 * @param object $object expects object that implements public $option property
 * @return void
 */
function setcooki_init_options($options, $object)
{
    if(setcooki_can_options($object))
    {
        foreach((array)$options as $k => $v)
        {
            $object->options[$k] = $v;
        }
    }
}


/**
 * set an option by name => value to object passed in third argument which is a class instance which implements public
 * $option property
 *
 * @param string $name expects the option name
 * @param mixed $value expects the option value
 * @param object $object expects object that implements public $option property
 * @return void
 */
function setcooki_set_option($name, $value, $object)
{
    if(setcooki_can_options($object))
    {
        $object->options[$name] = $value;
    }
}


/**
 * get an option by option name from object passed in second argument which is a class instance which implements public
 * $option property
 *
 * @param string $name expects the option name
 * @param object $object expects object that implements public $option property
 * @param null|mixed $default expects default return value
 * @return mixed
 */
function setcooki_get_option($name, $object, $default = null)
{
    if(setcooki_has_option($name, $object))
    {
        return $object->options[$name];
    }
    return $default;
}


/**
 * get all options from object passed in first argument which is a class instance which implements public $option property
 *
 * @param object $object expects object that implements public $option property
 * @param null|mixed $default expects default return value
 * @return mixed
 */
function setcooki_get_options($object, $default = null)
{
    if(setcooki_can_options($object))
    {
        return $object->options;
    }
    return $default;
}


/**
 * check if an object, class instance which has a public $option property, has a array key under the name passed in first
 * argument. the third argument will check if the value for name is a valid with setcooki_is_value() function
 *
 * @param string $name expects the option name
 * @param object $object expects the object to check
 * @param bool $strict expects boolean value for strict mode or not
 * @return bool
 */
function setcooki_has_option($name, $object, $strict = false)
{
    if(setcooki_can_options($object))
    {
        if(array_key_exists($name, $object->options))
        {
            if((bool)$strict)
            {
                return (setcooki_is_value($object->options[$name])) ? true : false;
            }else{
                return true;
            }
        }
    }
    return false;
}


/**
 * check if object in first argument is a class that implements public property $options which must be an array
 *
 * @param object $object expects the object to test
 * @return bool
 */
function setcooki_can_options($object)
{
    return (is_object($object) && property_exists($object, 'options') && is_array(@$object->options)) ? true : false;
}


/**
 * multi needle implementation of php´s in_array function
 *
 * @param string|array $needle expects the needle to lookup in haystack
 * @param array $haystack expects array with values
 * @param bool $strict expects boolean value for strict mode
 * @return bool
 */
function setcooki_in_array($needle, $haystack, $strict = false)
{
    if(!is_array($needle))
    {
        return in_array($needle, $haystack, $strict);
    }else{
        return (count(array_intersect($needle, $haystack)) > 0) ? true : false;
    }
}


/**
 * typify or cast a string value back to native data type
 *
 * @param mixed $value expects the value to typify
 * @return bool|float|int|null|string
 */
function setcooki_typify($value)
{
    if(is_numeric($value) && (int)$value <= PHP_INT_MAX)
    {
        if((int)$value != $value){

            return (float)$value;
        }else if(filter_var($value, FILTER_VALIDATE_INT) !== false){
            return (int)$value;
        }else{
            return strval($value);
        }
    }else{
        if($value === 'true' || $value === 'TRUE')
        {
            return true;
        }else if($value === 'false' || $value === 'false'){
            return false;
        }else if($value === 'null' || $value === 'NULL'){
            return null;
        }else{
            return strval($value);
        }
    }
}


/**
 * array implementation of setcooki_typify()
 *
 * @see setcooki_typify()
 * @param array $array expects array to typify values
 * @return array
 */
function setcooki_typify_array(Array &$array)
{
    foreach($array as $k => &$v)
    {
        if(is_array($v))
        {
            setcooki_typify_array($v);
        }else{
            $v = setcooki_typify($v);
        }
    }
    return $array;
}


/**
 * dynamic implementation of php´s sprintf function which excepts an array or multiple arguments as string replacements
 * for string in first argument
 *
 * @param string $string expects the string to replace
 * @param null|mixed $params expects placeholder values
 * @return string
 */
function setcooki_sprintf($string, $params = null)
{
    if(func_num_args() > 2)
    {
        $params = array_slice(func_get_args(), 1);
    }
    $params = (array)$params;
    if(sizeof($params) > 0)
    {
        return vsprintf((string)$string, array_values($params));
    }else{
        return $string;
    }
}


/**
 * detect links in plain text input in first argument and replace them with html representation ergo clickable links
 *
 * @param string $string expects text to linkify
 * @param null $target expects option link window target value
 * @return mixed
 */
function setcooki_linkify($string, $target = null)
{
    $pattern = '=((www\.|https?\:\/\/)[^\s]+)=i';

    if(preg_match($pattern, $string))
    {
        if($target !== null)
        {
            $string = preg_replace($pattern, '<a href="$1" target="'.(string)$target.'">$1</a>', $string);
        }else{
            $string = preg_replace($pattern, '<a href="$1">$1</a>', $string);
        }
    }
    if(stripos($string, '@') !== false)
    {
        $string = preg_replace('=([a-zA-Z0-9_\-\.]*@\\S+\\.\\w+)=i', '<a href="mailto:$1">$1</a>', $string);
    }
    return $string;
}


/**
 * checks if a variable can be stringified using data type casting to string
 *
 * @param mixed $mixed expects a variable value
 * @return bool
 */
function setcooki_stringable($mixed)
{
    return (is_array($mixed) || is_object($mixed) || is_callable($mixed) || is_resource($mixed)) ? false : true;
}


/**
 * return wordpress version or compare wordpress version with version passed as second argument returning boolean value
 * if version compare matches
 *
 * @param null|string $version expects optional version string
 * @param string $operator expects optional compare operator
 * @return mixed
 */
function setcooki_version($version = null, $operator = '>=')
{
    if(is_null($version))
    {
        return version_compare($GLOBALS['wp_version'], trim((string)$version), trim((string)$operator));
    }else{
        return $GLOBALS['wp_version'];
    }
}


/**
 * removes regex pattern delimiters including modifiers from pattern so the passed pattern can be placed inside php
 * regex function with already existing delimiters. the second argument will also allow for trimming of any chars and
 * beginning and end of pattern usually meta characters like ^$
 *
 * @param string $pattern expects the pattern to remove delimiters from
 * @param string $trim expects optional trim values
 * @return string
 */
function setcooki_regex_delimit($pattern, $trim = '')
{
    $pattern = preg_replace('=^([^\s\w\\\]{1})([^\\1]*)\\1(?:[imsxeADSUXJu]*)?$=i', '\\2', trim((string)$pattern));
    $pattern = trim($pattern, " " .trim($trim));
    return $pattern;
}