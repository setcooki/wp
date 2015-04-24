<?php

/**
 * @param $object
 * @param null $key
 * @param null $default
 * @return array|mixed|object
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
 * @param $object
 * @param null $key
 * @param null $value
 * @return null
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
 * @param $object
 * @param null $key
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
 * @param array $array
 * @param null $key
 * @param bool $strict
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
 * @param $value
 * @return array|object
 */
function setcooki_array_to_object($value)
{
    if(is_array($value))
    {
        if(array_keys($value) === range(0, count($value) - 1))
        {
            return (array)array_map(__FUNCTION__, $value);
        }else{
            return (object)array_map(__FUNCTION__, $value);
        }
    }else{
        return $value;
    }
}


/**
 * @param $value
 * @return array
 */
function setcooki_object_to_array($value)
{
    if(is_object($value))
    {
        $value = get_object_vars($value);
    }
    if(is_array($value))
    {
   	    return array_map(__FUNCTION__, $value);
    }else{
   		return $value;
   	}
}


/**
 * @param null $mixed
 * @return bool
 */
function setcooki_is_value($mixed = null)
{
    if(is_null($mixed))
    {
        return false;
    }
    if(is_bool($mixed) && $mixed === false)
    {
        return false;
    }
    if(is_array($mixed) && empty($mixed))
    {
        return false;
    }
    if(is_string($mixed) && $mixed === '')
    {
        return false;
    }
    return true;
}

/**
 * @param $value
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
 * @param $options
 * @param $object
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
 * @param $name
 * @param $value
 * @param $object
 */
function setcooki_set_option($name, $value, $object)
{
    if(setcooki_can_options($object))
    {
        $object->options[$name] = $value;
    }
}

/**
 * @param $name
 * @param $object
 * @param null $default
 * @return null
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
 * @param $object
 * @param null $default
 * @return null
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
 * @param $name
 * @param $object
 * @param bool $strict
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
                return (!empty($object->options[$name])) ? true : false;
            }else{
                return true;
            }
        }
    }
    return false;
}

/**
 * @param $object
 * @return bool
 */
function setcooki_can_options($object)
{
    return (is_object($object) && property_exists($object, 'options')) ? true : false;
}

/**
 * @param $needle
 * @param $haystack
 * @param bool $strict
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
 * @param $value
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
 * @param array $array
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
 * @param $string
 * @param null $params
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
 * @param $string
 * @param null $target
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