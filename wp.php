<?php

/**
 * @param $options
 * @param null $selected
 * @param null $exclude
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
 * @param null $selected
 * @param null $exclude
 */
function setcooki_dropdown_roles($selected = null, $exclude = null)
{
    $html = array();
    $roles = get_editable_roles();

   	foreach($roles as $key => $value)
    {
        if(in_array($key, (array)$exclude))
        {
            continue;
        }
   		$name = translate_user_role($value['name']);
        if(in_array($key, (array)$selected))
        {
            $html[] = "\t<option selected='selected' value='" . esc_attr($key) . "'>$name</option>";
        }else{
            $html[] = "\t<option value='" . esc_attr($key) . "'>$name</option>";
        }
   	}
   	echo implode("\n", $html);
}