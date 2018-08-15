<?php

if(!function_exists('setcooki_object_get'))
{
    /**
     * get values from objects/array by key/path
     *
     * @param object|array $object expects object to get value from
     * @param null|string $key expects a key to look up in object or null to return the object
     * @param null|mixed $default expects default return value
     * @return mixed
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
                if(is_object($object))
                {
                    $object = setcooki_object_to_array($object);
                }
            }
        }
        return ($o) ? setcooki_array_to_object($object) : $object;
    }
}


if(!function_exists('setcooki_object_set'))
{
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
                $object[$key] = [];
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
}


if(!function_exists('setcooki_object_unset'))
{
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
            $object = [];
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
}


if(!function_exists('setcooki_object_isset'))
{
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
}


if(!function_exists('setcooki_array_to_object'))
{
    /**
     * convert array to std object
     *
     * @param array|mixed $array the array to convert
     * @param mixed $default expects default return value
     * @return object|mixed
     */
    function setcooki_array_to_object($array, $default = null)
    {
        if(($array = json_encode($array)) !== false)
        {
            return json_decode($array, false);
        }else{
            return setcooki_default($default);
        }
    }
}


if(!function_exists('setcooki_array_is_assoc'))
{
    /**
     * check if an array has associative keys or not
     *
     * @since 1.2
     * @param array $array expects array to check
     * @return bool
     */
    function setcooki_array_is_assoc(Array $array)
    {
        if (!is_array($array)) return false;
        if (array() === $array) return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }
}


if(!function_exists('setcooki_object_to_array'))
{
    /**
     * convert std object to array
     *
     * @param object|mixed $object expects object to convert
     * @param mixed $default expects default return value
     * @return array|mixed
     */
    function setcooki_object_to_array($object, $default = null)
    {
        if(($object = json_encode($object)) !== false)
        {
            return json_decode($object, true);
        }else{
            return setcooki_default($default);
        }
    }
}


if(!function_exists('setcooki_is_value'))
{
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
}

if(!function_exists('setcooki_type'))
{
    /**
     * returns data type value as string of variable passed in first parameter. if the second parameter is to true will
     * convert string values that are actually wrong casted into its proper data type - usually used on database results
     * or $_GET parameters e.g.
     *
     * @since 1.2
     * @param null|mixed $value expects the variable to test
     * @param boolean $convert expects boolean value for converting string
     * @return null|string
     */
    function setcooki_type($value = null, $convert = false)
    {
        if(is_string($value) && (bool)$convert)
        {
            if(is_numeric($value))
            {
                if((float)$value != (int)$value){
                    $value = (float)$value;
                }else{
                    $value = (int)$value;
               }
            }else{
                if($value === 'true' || $value === 'false')
                {
                    $value = (bool)$value;
                }
            }
        }

        if(is_object($value)){
            return 'object';
        }
        if(is_array($value)){
            return 'array';
        }
        if(is_resource($value)){
            return 'resource';
        }
        if(is_callable($value)){
            return 'callable';
        }
        if(is_file($value)){
            return 'file';
        }
        if(is_int($value)){
            return 'integer';
        }
        if(is_float($value)){
            return 'float';
        }
        if(is_bool($value)){
            return 'boolean';
        }
        if(is_null($value)){
            return 'null';
        }
        if(is_string($value)){
            return 'string';
        }
        return null;
    }
}


if(!function_exists('setcooki_is'))
{
    /**
     * check if a value is of data type as defined in native php types or setcooki wp types defined as SETCOOKI_TYPE_*
     * constants in core.php
     *
     * @since 1.2
     * @param null|mixed $type expects the data type
     * @param null $value expects the value to check
     * @return bool
     */
    function setcooki_is($type, $value = null)
    {
        $type = strtolower(trim($type));
        if(function_exists("is_$type"))
        {
            return (bool)call_user_func("is_$type", $value);
        }
        if(function_exists("setcooki_is_$type"))
        {
            return (bool)call_user_func("setcooki_is_$type", $value);
        }
        return false;
    }
}

if(!function_exists('setcooki_is_class'))
{
    /**
     * check if class value passed in first argument is a regular and instantiable class or not
     *
     * @since 1.2
     * @param mixed $class
     * @return bool
     */
    function setcooki_is_class($class)
    {
        if(is_object($class))
        {
            return true;
        }else{
            return (class_exists((string)$class)) ? true : false;
        }
    }
}

if(!function_exists('setcooki_is_class'))
{
    /**
     * check if date in first argument is a valid date as checked by PHP´s native strtotime() method or if second argument
     * is set to true by date format set in global constant SETCOOKI_WP_DATE_FORMAT
     *
     * @since 1.2
     * @param string $date expects the date to check
     * @param bool $strict expects whether check strict against SETCOOKI_WP_DATE_FORMAT
     * @return bool
     */
    function setcooki_is_date($date, $strict = false)
    {
        if((bool)$strict)
        {
            return (bool)@strptime((string)$date, (string)SETCOOKI_WP_DATE_FORMAT);
        }else{
            return (bool)@strtotime((string)$date);
        }
    }
}


if(!function_exists('setcooki_is_datetime'))
{
    /**
     * check if date time value in first argument is a valid date as checked by PHP´s native strtotime() method or if
     * second argument is set to true by date format set in global constant SETCOOKI_WP_DATETIME_FORMAT
     *
     * @since 1.2
     * @param string $datetime expects the date time to check
     * @param bool $strict expects whether check strict against SETCOOKI_WP_DATETIME_FORMAT
     * @return bool
     */
    function setcooki_is_datetime($datetime, $strict = false)
    {
        if((bool)$strict)
        {
            return (bool)@strptime($datetime, (string)SETCOOKI_WP_DATETIME_FORMAT);
        }else{
            return (bool)@strtotime((string)$datetime);
        }
    }
}


if(!function_exists('setcooki_is_timestamp'))
{
    /**
     * check if timestamp in first argument is a valid unix timestamp that can be processed by host system. if second parameter
     * is set will check also if timestamp is > time value passed in second parameter
     *
     * @since 1.2
     * @param int|string $timestamp expects the timestamp
     * @param null $time
     * @return bool
     */
    function setcooki_is_timestamp($timestamp, $time = null)
    {
        $check = null;

        if(is_int($timestamp) || is_float($timestamp))
        {
            $check = $timestamp;
        }else{
            $check = (string)(int)$timestamp;
        }
        $return = ($check === $timestamp) && ((int)$timestamp <= PHP_INT_MAX) && ((int)$timestamp >= ~PHP_INT_MAX);
        if($return && $time !== null)
        {
            if($time === true) $time = time();
            return ((int)$timestamp >= (int)$time) ? true : false;
        }
        return false;
    }
}


if(!function_exists('setcooki_default'))
{
    /**
     * default value function that will take a mixed value as argument and executes it according to values data type which
     * can be a php callback, exception, exit command or default return string
     *
     * @param mixed $value expects the value to execute
     * @return mixed
     * @throws \Exception
     */
    function setcooki_default($value)
    {
        if($value instanceof \Exception)
        {
            throw $value;
        }else if(is_callable($value) || (is_string($value) && function_exists($value))){
            return call_user_func($value);
        }else if($value === 'exit'){
            exit(0);
        }
        return $value;
    }
}


if(!function_exists('setcooki_init_options'))
{
    /**
     * init class options by passing options array in first argument and class instance in second argument. the class
     * option will be set to $object->option property which must be public. if the object/class in second argument has a
     * static property named $optionsMap will validate the options against the map. the map must be an array of option name
     * as key and option data type as value. if the class is a subclass the options of the parent classes will be merged
     * into the option array in order to initialize parent classes with inherent options too.
     *
     * @since 1.2 implements options map validating
     * @param array|null $options expects option array
     * @param object $object expects object that implements public $option property
     * @return void
     * @throws Exception
     */
    function setcooki_init_options(Array $options = null, $object)
    {
        if(setcooki_can_options($object) && !empty($options))
        {
            $map = 'optionsMap';
            $class = null;
            $instance = null;
            $_options = [];
            $class = get_class($object);
            $parents = class_parents($object);

            if(property_exists($object, $map))
            {
                $map = (array)$class::$$map;
            }else{
                $map = [];
            }
            if(!empty($parents))
            {
                foreach($parents as $parent)
                {
                    try
                    {
                        $class = new \ReflectionClass($parent);
                        if(!$class->isAbstract() && !$class->isInterface() && !$class->isInternal())
                        {
                            if(($_map = $class->getStaticPropertyValue('optionsMap', false)) !== false)
                            {
                                $map = array_merge($map, (array)$_map);
                            }
                            $instance = $class->newInstanceWithoutConstructor();
                            if($class->hasProperty('options'))
                            {
                                $property = $class->getProperty('options');
                                $_options = array_merge($_options, (array)$property->getValue($instance));
                            }
                        }
                    }
                    catch(\Exception $e){}
                }
            }

            if(!empty($map))
            {
                foreach($map as $name => $type)
                {
                    if(array_key_exists($name, $options))
                    {
                        $ok = 0;
                        $error = [];
                        if(!is_array($type))
                        {
                            $type = [$type];
                        }
                        foreach($type as $t)
                        {
                            try
                            {
                                if(defined('SETCOOKI_TYPE_' . strtoupper($t)))
                                {
                                    if(strtoupper($t) !== SETCOOKI_TYPE_MIXED && !setcooki_is($t, $options[$name]) )
                                    {
                                        throw new Exception(setcooki_sprintf("option: %s expects data type: %s", $name, implode('|', $type)));
                                    }else{
                                        $ok++;
                                    }
                                }else{
                                    if(stripos($t, NAMESPACE_SEPARATOR) !== false && (!class_exists($options[$name]) || !($options[$name] instanceof $t)))
                                    {
                                        throw new Exception(setcooki_sprintf("unable to set option: %s since value must be instance of: %s", $name, implode('|', $type)));
                                    }else{
                                        $ok++;
                                    }
                                }
                            }
                            catch(Exception $e)
                            {
                                array_push($error, $e);
                            }
                        }
                        if($ok === 0)
                        {
                            throw $error[0];
                        }
                    }
                }
            }

            $options = array_merge($_options, (array)$options);
            foreach((array)$options as $k => $v)
            {
                setcooki_set_option($k, $v, $object);
            }
        }
    }
}


if(!function_exists('setcooki_set_options'))
{
    /**
     * set options array overriding previously set options and bypassing option initing from setcooki_init_options()
     *
     * @since 1.2
     * @param array $options expects option array
     * @param object $object expects object that implements public $option property
     * @return void
     */
    function setcooki_set_options(Array $options, $object)
    {
        if(setcooki_can_options($object))
        {
            $object->options = $options;
        }
    }
}


if(!function_exists('setcooki_set_option'))
{
    /**
     * set an option by name => value to object passed in third argument which is a class instance which implements public
     * $option property. NOTE: that setting option values via this method does bypass the data type validation from setcooki_init_options
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
}


if(!function_exists('setcooki_get_option'))
{
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
}


if(!function_exists('setcooki_get_options'))
{
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
}


if(!function_exists('setcooki_has_option'))
{
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
}


if(!function_exists('setcooki_is_option'))
{
    /**
     * shortcut function to test if object has option which is a valid value - see setcooki_has_option()
     *
     * @since 1.2
     * @see setcooki_has_option()
     * @param string $name expects the option name
     * @param object $object expects the object to check
     * @return bool
     */
    function setcooki_is_option($name, $object)
    {
        return setcooki_has_option($name, $object, true);
    }
}


if(!function_exists('setcooki_can_options'))
{
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
}


if(!function_exists('setcooki_in_array'))
{
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
}


if(!function_exists('setcooki_typify'))
{
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
}


if(!function_exists('setcooki_typify_array'))
{
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
}


if(!function_exists('setcooki_sprintf'))
{
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
}


if(!function_exists('setcooki_linkify'))
{
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
}


if(!function_exists('setcooki_stringable'))
{
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
}


if(!function_exists('setcooki_version'))
{
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
}


if(!function_exists('setcooki_regex_delimit'))
{
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
}


if(!function_exists('setcooki_array_assoc'))
{
    /**
     * check if an array consists of numeric or associated array keys and returns boolean true if so
     *
     * @since 1.1.2
     * @param mixed $array
     * @return bool
     */
    function setcooki_array_assoc($array)
    {
        if(is_array($array))
        {
            return array_keys($array) !== range(0, count($array) - 1);
        }
        return false;
    }
}


if(!function_exists('setcooki_is_callable'))
{
    /**
     * check if a value is callable/closure
     *
     * @since 1.1.2
     * @param mixed $mixed expects value to test
     * @return bool
     */
    function setcooki_is_callable($mixed)
    {
        return (is_callable($mixed) || $mixed instanceof \Closure) ? true : false;
    }
}


if(!function_exists('setcooki_str_like'))
{
    /**
     * mysql LIKE % wildcard match function. checks if the string passed in first argument is LIKE pattern in second argument
     *
     * @since 1.1.2
     * @param string $string expects the string to check
     * @param string $like expects the like pattern
     * @return bool
     */
    function setcooki_str_like($string, $like)
    {
        $string = (string)$string;
        $like = (string)$like;
        if($like[0] === '%' && $like[strlen($like)-1] === '%') {
            $like = '@'.trim(setcooki_regex_delimit($like), ' %').'@i';
        }else if($like[0] === '%' && $like[strlen($like)-1] !== '%'){
            $like = '@'.trim(setcooki_regex_delimit($like), ' %').'$@i';
        }else if($like[0] !== '%' && $like[strlen($like)-1] === '%'){
            $like = '@^'.trim(setcooki_regex_delimit($like), ' %').'@i';
        }else{
            $like = '@^'.trim(setcooki_regex_delimit($like), ' %').'$@i';
        }
        return (bool)preg_match($like, $string);
    }
}


if(!function_exists('setcooki_nonce'))
{
    /**
     * create a wordpress style nonce value with random string or if you pass action string in first argument with
     * \Setcooki\Wp\Util\Nonce class
     *
     * @since 1.2 added $action argument
     * @since 1.2 added $lifetime argument
     * @since 1.1.3
     * @param null|string $action expects the action string
     * @param int $lifetime expects optional nonce lifetime
     *
     * @return string
     */
    function setcooki_nonce($action = null, $lifetime = 1800)
    {
        if(!is_null($action))
        {
            return \Setcooki\Wp\Util\Nonce::create($action, $lifetime);
        }else{
            return wp_create_nonce(substr(substr("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", mt_rand(0 ,50), 1) . substr(md5(time()), 1), 0, 10));
        }
    }
}


if(!function_exists('setcooki_ajax_proxy'))
{
    /**
     * returns the proxy ajax action name which is set in ajax base controller
     *
     * @since 1.2
     * @return string
     */
    function setcooki_ajax_proxy()
    {
        try
        {
            return (string)setcooki_wp()->store('ajax.proxy.hook', null, 'proxy');
        }
        catch(\Exception $e)
        {
            return 'proxy';
        }
    }
}


if(!function_exists('setcooki_ajax_url'))
{
    /**
     * preferred way to get admin ajax url. if the first argument is an array with key => value pairs will add
     * these to the url as GET parameter as well
     *
     * @since 1.2
     * @param null|array $params optional parameter to add to url
     * @return string
     */
    function setcooki_ajax_url(Array $params = null)
    {
        $args = [];
        $url = admin_url('admin-ajax.php');

        $id = null;
        try
        {
            $id = setcooki_id(true, '');
        }
        catch(\Exception $e){}
        if(!empty($id))
        {
            $args = ['_id' => $id];
        }
        $args['_ts'] = time();

        if(!empty($params) && (array_keys($params) !== range(0, count($params) - 1)))
        {
            $args = array_merge($args, (array)$params);
        }
        if(stripos($url, '?') === false)
        {
            $url .= '?';
        }else{
            $url .= '&';
        }
        $url .= http_build_query($args);
        return $url;
    }
}


if(!function_exists('setcooki_basename'))
{
    /**
     * more flexible implementation of php´s basename function. accepts file extension suffix as wildcard ".*"
     *
     * @since 1.1.3
     * @param string $path expects the path
     * @param null|string $suffix expects the optional file extension suffix to remove
     * @return string
     */
    function setcooki_basename($path, $suffix = null)
    {
        $path = basename($path);
        if(!is_null($suffix))
        {
            if($suffix === '.*')
            {
                $path = substr($path, 0, strripos($path, '.'));
            }else if($suffix === '*.*'){
                $path = substr($path, 0, stripos($path, '.'));
            }else{
                $path = basename($path, $suffix);
            }
        }
        return $path;
    }
}


if(!function_exists('setcooki_url'))
{
    /**
     * return the full current url or a fragment of the same if first argument is a integer part flag as used in php´s
     * parse url function. if you pass integer -1 will return all parts as array just like native function. returns boolean
     * false on failure
     *
     * @since 1.1.4
     * @see parse_url()
     * @param null|int $part expects the part to return see php parse_url function for allowed flags
     * @return bool|mixed|string
     */
    function setcooki_url($part = null)
    {
        $ssl        = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on');
        $protocol   = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/' )) . (($ssl) ? 's' : '');
        $port       = ((!$ssl && trim($_SERVER['SERVER_PORT']) === '80') || ($ssl && trim($_SERVER['SERVER_PORT']) === '443')) ? '' : ':' . trim($_SERVER['SERVER_PORT']);
        $host       = (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) ? trim((string)$_SERVER['HTTP_HOST']) : trim($_SERVER['SERVER_NAME']) . $port;
        $url        = $protocol . '://' . $host . '/' . trim($_SERVER['REQUEST_URI'], ' /');

        if(!is_null($part))
        {
            $part = (int)$part;
            if($part === -1)
            {
                return parse_url($url);
            }else{
                if(($url = parse_url($url, $part)) !== false)
                {
                    if($part === PHP_URL_PATH)
                    {
                        $url = trim($url, ' /');
                    }
                    return $url;
                }else{
                    return false;
                }
            }
        }else{
            return $url;
        }
    }
}


if(!function_exists('setcooki_reset_query'))
{
    /**
     * reset wordpress $wp_query object
     *
     * @since 1.2
     * @returns void
     */
    function setcooki_reset_query()
    {
        global $wp_query;

        $wp_query->is_single = false;
        $wp_query->is_page = false;
        $wp_query->queried_object = null;
        $wp_query->is_home = false;
    }
}


if(!function_exists('setcooki_dump'))
{
    /**
     * generic dump function will try to dump any input in first parameter using the passed value in second parameter
     * or if not set by default echo and print_r in cli or none cli mode. the first parameter can by anything that can be
     * printed to screen via print_r function. the second parameter can by a php function, an object or class name the
     * implements the dump method as public static or none static method. the dump method of object must have its own logic
     * for dumping objects since this function will do nothing else but calling the method returning void. you can also
     * use a php function like json_encode in second parameter to encode and dump your input

     *
     * @since 1.2
     * @param mixed $what expects any type of variable
     * @param null|string|callable|object $with expects optional value with what to output first parameter
     * @return void
     */
    function setcooki_dump($what, $with = null)
    {
        ob_start();

        if($with !== null)
        {
            if(is_callable($with))
            {
                echo call_user_func($with, $what);
            }else if(is_object($with) && method_exists($with, 'dump')){
                echo $with->dump($what);
            }else if(is_string($with)){
                if(method_exists($with, 'dump'))
                {
                    echo call_user_func([$with, 'dump'], $what);
                }
            }
        }
        if(strlen($o = (string)ob_get_contents()) > 0)
        {
            ob_end_clean();
            echo $o;
        }else{
            @ob_end_clean();
            echo ((strtolower(php_sapi_name()) === 'cli') ? print_r($what, true) : "<pre>".print_r($what, true)."</pre>");
        }
    }
}


if(!function_exists('setcooki_shortcode_forward'))
{
    /**
     * forward the execution of a shortcode tag to another shortcode tag already registered
     *
     * @since 1.2
     * @param string $shortcode expects the shortcode tag that needs to be forwarded
     * @param string $target expects the shortcode tag that needs to be executed instead of the forward source
     * @return null|string
     * @throws Exception
     */
    function setcooki_shortcode_forward($shortcode, $target)
    {
        return setcooki_shortcode($shortcode, function($atts, $content = null) use ($target)
        {
            global $shortcode_tags;

            $target = trim($target, ' []');
            if(!isset($shortcode_tags[$target]))
            {
                return null;
            }
            return call_user_func($shortcode_tags[$target], (array)$atts, $content, $target);
        });
    }
}
