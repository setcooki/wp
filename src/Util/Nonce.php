<?php

namespace Setcooki\Wp\Util;

use Setcooki\Wp\Exception;

/**
 * Class Nonce
 *
 * @since       1.2
 * @package     Setcooki\Wp\Util
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Nonce
{
    /**
     * default nonce lifetime value
     */
    const LIFETIME = 1800;

    /**
     * contains the action value
     *
     * @var string
     */
    protected $_action = '';


    /**
     * contains the lifetime value
     *
     * @var int
     */
    protected $_lifetime = self::LIFETIME;


    /**
     * create new nonce object with action name and optional lifetime
     *
     * @param string $action expects the action name
     * @param int $lifetime expects the lifetime
     */
    public function __construct($action, $lifetime = self::LIFETIME)
    {
        $this->action($action);
        $this->lifetime($lifetime);
    }


    /**
     * static shortcut method to create the nonce string
     *
     * @param string $action expects the action name
     * @param int $lifetime expects the lifetime
     * @return string
     */
    public static function create($action, $lifetime = self::LIFETIME)
    {
        return (string)(new self($action, $lifetime));
    }


    /**
     * setter/getter for action name
     *
     * @param null|string $action expects the action name
     * @return string
     */
    public function action($action = null)
    {
        if($action !== null && !empty($action))
        {
            $this->_action = (string)$action;
        }
        return $this->_action;
    }


    /**
     * setter/getter for lifetime value
     *
     * @param null|int $lifetime expects the lifetime value
     * @return int
     */
    public function lifetime($lifetime = null)
    {
        if($lifetime !== null)
        {
            $this->_lifetime = ((int)$lifetime > 0) ? (int)$lifetime : self::LIFETIME;
        }
        return $this->_lifetime;
    }


    /**
     * verify a nonce bound to action name in first argument. the nonce is retrieved from $_REQUEST, or $_POST or $_GET
     * and used to verify with wordpress wp_verify_nonce() method. the first argument can also be a Setcooki\Wp\Util\Nonce
     * object which contains the action name and lifetime
     *
     * @param string|Nonce $action expects the action name
     * @param int $lifetime expects the optional lifetime
     * @return bool|false
     */
    public static function verify($action, $lifetime = self::LIFETIME)
    {
        if(isset($_REQUEST['nonce']) && !empty($_REQUEST['nonce']))
        {
            $nonce = trim($_REQUEST['nonce']);
        }else if(isset($_POST['nonce']) && !empty($_POST['nonce'])){
            $nonce = trim($_POST['nonce']);
        }else if(isset($_GET['nonce']) && !empty($_GET['nonce'])){
            $nonce = trim($_GET['nonce']);
        }else{
            return false;
        }
        if($action instanceof Nonce)
        {
            $lifetime = $action->lifetime();
            $action = $action->action();
        }
        $filter = function() use ($lifetime)
        {
            return (int)$lifetime;
        };
        add_filter('nonce_life', $filter);
        $valid = wp_verify_nonce($nonce, $action);
        remove_filter('nonce_life', $filter);
        unset($filter);
        return $valid;
    }


    /**
     * make hash from action name
     *
     * @param string $action expects the action name
     * @return string
     */
    public static function hash($action)
    {
        $id = '';
        try
        {
            if(isset($_REQUEST['_id']) && !empty($_REQUEST['_id']))
            {
                $id = trim($_REQUEST['_id']);
            }else if(isset($_GET['_id']) && !empty($_GET['_id'])){
                $id = trim($_GET['_id']);
            }else if(isset($_POST['_id']) && !empty($_POST['_id'])){
                $id = trim($_POST['_id']);
            }else{
                $id = setcooki_id(true);
            }
        }
        catch(\Exception $e){}
        return wp_hash((string)$action . $id . get_current_blog_id(), 'nonce');
    }


    /**
     * create the actual nonce by nonce object to string casting
     *
     * @return string
     */
    public function __toString()
    {
        $filter = function()
        {
            return (int)$this->_lifetime;
        };
        add_filter('nonce_life', $filter);
        $nonce = wp_create_nonce($this->_action);
        remove_filter('nonce_life', $filter);
        unset($filter);
        return $nonce;
    }
}
