<?php

if(!function_exists('setcooki_dropdown'))
{
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
        $html = [];
    
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
}


if(!function_exists('setcooki_excerpt'))
{
    /**
     * generate excerpt = string cut off a length defined in second argument from string, post, or post id
     *
     * @param mixed $mixed expects post id, post object or text string
     * @param null|int $length expects the desired excerpt approx length
     * @param null|string|callable $wrap expects a string, a string with sprintf % placeholder or a callable
     * @param bool|false $html expects boolean flag for whether strip html or not
     * @param bool|null|string $return expects a flag for whether to return or echo output or reference to a string object
     * @return string
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
                $excerpt = call_user_func_array($wrap, [$excerpt]);
            }else if(is_string($wrap) && preg_match('=\%(s|d)=i', $wrap)){
                $excerpt = vsprintf($wrap, [$excerpt]);
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
}


if(!function_exists('setcooki_loop'))
{
    /**
     * automated generalized post loop function that will loop over posts in globals and use a partial/template in first
     * argument for rendering. the first argument can also be a callback expecting to return html for outputting. optional
     * parameters can be passed in second argument just like with setcooki_include function. the main query can be overriden
     * with third argument which can be instance of WP_Query, array of query arguments or callable which expects a new Wp_Query
     * object in return
     *
     * @param string|callable $partial expects a path to partial or callback
     * @param null|mixed $params expects optional params
     * @param null|WP_Query|array|callable $query expects optional query object
     * @return void
     */
    function setcooki_loop($partial, $params = null, $query = null)
    {
        if($query instanceof \WP_Query)
        {
            $GLOBALS['wp_query'] = $query;
        }else if(is_array($query)){
            $GLOBALS['wp_query'] = new \WP_Query($query);
        }else if(is_callable($query)){
            $GLOBALS['wp_query'] = call_user_func_array($query, [$params]);
        }else{
            //do nothing query is set already
        }
        if(have_posts())
        {
            while(have_posts())
            {
                the_post();
                global $post;
                if(is_callable($partial))
                {
                    echo call_user_func_array($partial, [$post, $params]);
                }else if(!is_null($partial)){
                    setcooki_include($partial, $params);
                }else{
                    //not defined yet could be controller
                }
            }
        }
        wp_reset_query();
        wp_reset_postdata();
        unset($post);
    }
}