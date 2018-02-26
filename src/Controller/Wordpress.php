<?php

namespace Setcooki\Wp\Controller;

use Setcooki\Wp\Exception;

/**
 * Class Wordpress
 *
 * @package     Setcooki\Wp\Controller
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Wordpress extends Controller
{
    /**
     * check if controller called in /wp-admin via is_admin()
     *
     * @since 1.2
     * @see is_admin()
     * @return bool
     */
    public function isAdmin()
    {
        return (is_admin()) ? true : false;
    }


    /**
     * check if controller called from frontend via is_admin()
     *
     * @since 1.2
     * @see is_admin()
     * @return bool
     */
    public function isFront()
    {
        return (!is_admin()) ? true : false;
    }


    /**
     * check whether a user is logged in via is_user_logged_in()
     *
     * @since 1.2
     * @see is_user_logged_in()
     * @return bool
     */
    public function hasUser()
    {
        return is_user_logged_in();
    }


    /**
     * get current logged in user if not default value
     *
     * @since 1.2
     * @param null|mixed $default expects optional default return value
     * @return mixed|\WP_User
     * @throws \Exception
     */
    public function getUser($default = null)
    {
        $user = wp_get_current_user();
        if($user->exists())
        {
            return $user;
        }else{
            return setcooki_default($default);
        }
    }
}