<?php

/**
 * populate a html select dropdown with option objects
 *
 * @param array|object $options expects options object
 * @param mixed $selected expects array or single value for selected option
 * @param mixed $exclude expects array or single values of to be excluded select option values
 * @return void
 */
function setcooki_dropdown($options, $selected = null, $exclude = null)
{
    $html = array();

    foreach((array)$options as $key => $value)
    {
        if(in_array($key, (array)$exclude))
        {
            continue;
        };
        if(in_array($key, (array)$selected))
        {
            $html[] = "\t<option selected='selected' value='" . esc_attr($key) . "'>$value</option>";
        }else{
            $html[] = "\t<option value='" . esc_attr($key) . "'>$value</option>";
        }
   	}
   	echo implode("\n", $html);
}


/**
 * generate excerpt = string cut off a length defined in second argument from string, post, or post id
 *
 * @param mixed $mixed expects post id, post object or text string
 * @param null|int $length expects the desired excerpt approx length
 * @param null|string|callable $wrap expects a string, a string with sprintf % placeholder or a callable
 * @param bool|false $html expects boolean flag for whether strip html or not
 * @param bool|null|string $return expects a flag for whether to return or echo output or reference to a string object
 * @return string|void
 */
function setcooki_excerpt($mixed, $length = null, $wrap = null, $html = false, &$return = null)
{
    if(is_string($mixed))
    {
        $excerpt = trim($mixed);
    }else{
        if(is_numeric($mixed))
        {
            $mixed = get_post((int)$mixed);
        }
        $excerpt = trim((string)apply_filters('get_the_excerpt', $mixed->post_excerpt));
        if(empty($excerpt))
        {
            $excerpt = apply_filters('the_content', get_post_field('post_content', $mixed));
        }
    }
    $excerpt = trim($excerpt);
    $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
    if(!(bool)$html)
    {
        $excerpt = strip_tags($excerpt);
    }
    if($length !== null && mb_strlen($excerpt) > (int)$length)
    {
        $excerpt = wordwrap($excerpt, $length, '<>');
        $excerpt = substr($excerpt, 0, strpos($excerpt, '<>'));
    }
    if(!is_null($wrap))
    {
        if(is_callable($wrap))
        {
            $excerpt = call_user_func_array($wrap, array($excerpt));
        }else if(is_string($wrap) && preg_match('=\%(s|d)=i', $wrap)){
            $excerpt = vsprintf($wrap, array($excerpt));
        }else{
            $excerpt = $excerpt . (string)$wrap;
        }
    }
    if($return === null)
    {
        echo $excerpt;
    }else if($return === true) {
        return $excerpt;
    }else if(is_string($return)){
        return $return = $excerpt;
    }else{
        return $excerpt;
    }
}