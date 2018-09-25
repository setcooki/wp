<?php

namespace Setcooki\Wp\Content\Document;

use Setcooki\Wp\Content\Document;

/**
 * TODO: needs documentation
 *
 * Class Html
 *
 * @since       1.2
 * @package     Setcooki\Wp\Content
 * @subpackage  Setcooki\Wp\Content\Document
 * @author      setcooki <set@cooki.me>
 * @copyright   setcooki <set@cooki.me>
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Html extends Document
{
    /**
     * close tags xhtml style
     */
    const XHTML_STYLE                   = 'XHTML_STYLE';

    /**
     * use \t tabs in head section
     */
    const HEAD_TABS                     = 'HEAD_TABS';


    /**
     * the option map
     *
     * @var array
     */
    public static $optionsMap =
    [
        self::XHTML_STYLE               => SETCOOKI_TYPE_BOOL,
        self::HEAD_TABS                 => SETCOOKI_TYPE_INT
    ];

    /**
   	 * contains class options
   	 *
   	 * @var array
   	 */
   	public $options =
    [
        self::XHTML_STYLE               => false,
        self::HEAD_TABS                 => 1
    ];


    /**
     * init html document by setting default document values
     *
     * @return $this
     */
    public function init()
    {
        $this
            ->setDocType('html')
            ->setHtmlLang(get_language_attributes())
            ->addMeta
            (
                ['charset' => get_bloginfo('charset')]
            );
        return $this;
    }


    /**
     * @param callable|null $callable
     * @return mixed|string
     */
    public function head(callable $callable = null)
    {
        if($callable !== null)
        {
            $this->set('head', $callable);
            return null;
        }else{
            $callable = $this->get('head', null);
            ob_start();
            wp_head();
            $head = ob_get_clean();
            if(!empty($callable))
            {
                return call_user_func($callable, $head);
            }else{
                return $head;
            }
        }
    }


    /**
     * @param callable|null $callable
     * @return mixed|string
     */
    public function footer(callable $callable = null)
    {
        if($callable !== null)
        {
            $this->set('footer', $callable);
            return null;
        }else{
            $callable = $this->get('footer', null);
            ob_start();
            wp_footer();
            $footer = ob_get_clean();
            if(!empty($callable))
            {
                return call_user_func($callable, $footer);
            }else{
                return $footer;
            }
        }
    }


    /**
     * @return mixed|null
     */
    protected function docType()
    {
        return $this->get('doc.type');
    }


    /**
     * @param $type
     * @return $this
     */
    public function setDocType($type)
    {
        $this->set('doc.type', $type);
        return $this;
    }


    /**
     * @return mixed|null
     */
    public function getDocType()
    {
        return $this->get('doc.type');
    }


    /**
     * @return mixed|null
     * @throws \Exception
     */
    protected function htmlLang()
    {
        return $this->get('html.lang');
    }


    /**
     * @param $lang
     * @return $this
     */
    public function setHtmlLang($lang)
    {
        $this->set('html.lang', $lang);
        return $this;
    }


    /**
     * @return mixed|null
     */
    public function getHtmlLang()
    {
        return $this->get('html.lang');
    }


    /**
     * @return mixed|null
     * @throws \Exception
     */
    protected function title()
    {
        return $this->get('title');
    }


    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->set('title', $title);
        return $this;
    }


    /**
     * @return mixed|null
     */
    public function getTitle()
    {
        return $this->get('title');
    }


    /**
     * @return mixed|null
     */
    protected function meta()
    {
        return $this->get('meta');
    }


    /**
     * @param array $meta
     * @return Html
     */
    public function addMeta(array $meta)
    {
        return $this->addContent('meta', $meta);
    }


    /**
     * @return mixed|null
     */
    public function getMeta()
    {
        return $this->get('meta');
    }


    /**
     * @param $pattern
     * @return Html
     */
    public function removeMeta($pattern)
    {
        return $this->removeContent('meta', $pattern);
    }


    /**
     * @return mixed|null
     */
    protected function link()
    {
        return $this->get('link');
    }


    /**
     * @param $href
     * @param array|null $attributes
     * @return Html
     */
    public function addLink($href, array $attributes = null)
    {
        $attributes = (array)$attributes;
        $attributes = array('href' => $href) + $attributes;
        return $this->addContent('link', $attributes);
    }


    /**
     * @return mixed|null
     */
    public function getLink()
    {
        return $this->get('link');
    }


    /**
     * @param $pattern
     * @return Html
     */
    public function removeLink($pattern)
    {
        return $this->removeContent('link', $pattern);
    }


    /**
     * @return mixed|null
     */
    protected function script()
    {
        return $this->get('script');
    }


    /**
     * @param $src
     * @param array|null $attributes
     * @return Html
     */
    public function addScript($src, array $attributes = null)
    {
        $attributes = (array)$attributes;
        if(!array_key_exists('type', $attributes))
        {
            $attributes = array('type' => 'application/javascript') + $attributes;
        }
        $attributes = array('src' => $src) + $attributes;

        return $this->addContent('script', $attributes);
    }


    /**
     * @return mixed|null
     */
    public function getScript()
    {
        return $this->get('script');
    }


    /**
     * @param $pattern
     * @return Html
     */
    public function removeScript($pattern)
    {
        return $this->removeContent('script', $pattern);
    }


    /**
     * @return mixed|null
     */
    protected function style()
    {
        return $this->get('style');
    }


    /**
     * @param $href
     * @param array|null $attributes
     * @return Html
     */
    public function addStyle($href, array $attributes = null)
    {
        $attributes = (array)$attributes;
        $attributes = array('href' => $href) + $attributes;
        if(!array_key_exists('type', $attributes))
        {
            $attributes = array('type' => 'text/css') + $attributes;
        }
        if(!array_key_exists('rel', $attributes))
        {
            $attributes = array('rel' => 'stylesheet') + $attributes;
        }

        return $this->addContent('style', $attributes, 'link');
    }


    /**
     * @return mixed|null
     */
    public function getStyle()
    {
        return $this->get('style');
    }


    /**
     * @param $pattern
     * @return Html
     */
    public function removeStyle($pattern)
    {
        return $this->removeContent('style', $pattern);
    }


    /**
     * @return string
     */
    protected function inlineStyle()
    {
        $html = [];

        $styles = (array)$this->get('inline.style', array());
        if(!empty($styles))
        {
            foreach($styles as $style)
            {
                $html[] = trim($style);
            }
            $t = str_repeat("\t", setcooki_get_option(self::HEAD_TABS, $this, 0));
            $tag = "<style type=\"text/css\">\n%s<!--\n%s\t%s\n%s-->\n%s</style>\n";
            return vsprintf($tag,
            [
                $t,
                $t,
                implode("\r\n$t\t", $html),
                $t,
                $t
            ]);
        }
        return '';
    }


    /**
     * @param $id
     * @param $style
     * @return $this
     */
    public function addInlineStyle($id, $style)
    {
        $styles = (array)$this->get('inline.style', array());
        $styles[$id] = $style;
        $this->set('inline.style', $styles);
        return $this;
    }


    /**
     * @return mixed|null
     */
    public function getInlineStyle()
    {
        return $this->get('inline.style');
    }


    /**
     * @param $id
     * @return $this
     */
    public function removeInlineStyle($id)
    {
        $tmp = array();

        $styles = (array)$this->get('inline.style', array());
        foreach($styles as $key => $val)
        {
            if($key === $id)
            {
                continue;
            }
            $tmp[$key] = $val;
        }
        $this->set('inline.style', $tmp);
        return $this;
    }


    /**
     * @return string
     */
    public function inlineScript()
    {
        $html = [];

        $scripts = (array)$this->get('inline.script', array());
        if(!empty($scripts))
        {
            foreach($scripts as $script)
            {
                $html[] = trim($script);
            }
            $t = str_repeat("\t", setcooki_get_option(self::HEAD_TABS, $this, 0));
            $tag = "<script type=\"application/javascript\">\n%s<!--\n%s\t%s\n%s//-->\n%s</script>\n";
            return vsprintf($tag,
            [
                $t,
                $t,
                implode("\r\n$t\t", $html),
                $t,
                $t
            ]);
        }
        return '';
    }


    /**
     * @param $id
     * @param $script
     * @return $this
     */
    public function addInlineScript($id, $script)
    {
        $scripts = (array)$this->get('inline.script', array());
        $scripts[$id] = $script;
        $this->set('inline.script', $scripts);
        return $this;
    }


    /**
     * @return mixed|null
     */
    public function getInlineScript()
    {
        return $this->get('inline.script');
    }


    /**
     * @param $id
     * @return $this
     */
    public function removeInlineScript($id)
    {
        $tmp = array();

        $scripts = (array)$this->get('inline.script', array());
        foreach($scripts as $key => $val)
        {
            if($key === $id)
            {
                continue;
            }
            $tmp[$key] = $val;
        }
        $this->set('inline.script', $tmp);
        return $this;
    }


    /**
     * @return string
     */
    public function bodyClass()
    {
        return join(' ', get_body_class($this->get('body.class')));
    }


    /**
     * @param $class
     */
    public function addBodyClass($class)
    {
        $classes = (array)$this->get('body.class', []);
        foreach((array)$class as $c)
        {
            if(!in_array($c, $classes))
            {
                $classes[] = $c;
            }
        }
        $this->set('body.class', $classes);
    }


    /**
     * @return mixed|null
     */
    public function getBodyClass()
    {
        return $this->get('body.class');
    }


    /**
     * @param $class
     */
    public function removeBodyClass($class)
    {
        $tmp = [];

        $classes = (array)$this->get('body.class', []);
        foreach((array)$class as $c)
        {
            if(in_array($c, $classes))
            {
                continue;
            }
            $tmp[] = $c;
        }
        $this->set('body.class', $tmp);
    }


    /**
     * @param $handle
     * @param $src
     * @param array $deps
     * @param bool $ver
     * @param bool $in_footer
     * @return $this
     */
    public function enqueueScript($handle, $src, $deps = [], $ver = false, $in_footer = false )
    {
        wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
        return $this;
    }


    /**
     * @param $handle
     * @return $this
     */
    public function dequeueScript($handle)
    {
        wp_dequeue_script($handle);
        return $this;
    }


    /**
     * @param $handle
     * @param $src
     * @param array $deps
     * @param bool $ver
     * @param string $media
     * @return $this
     */
    public function enqueueStyle($handle, $src, $deps = [], $ver = false, $media = 'all' )
    {
        wp_enqueue_style($handle, $src, $deps, $ver, $media);
        return $this;
    }


    /**
     * @param $handle
     */
    public function dequeueStyle($handle)
    {
        wp_dequeue_style($handle);
    }


    /**
     * @param $for
     * @param array $with
     * @param null $alias
     * @return $this
     */
    protected function addContent($for, array $with, $alias = null)
    {
        $this->addTo($for, $this->tag((($alias) ? $alias : $for), $with));
        return $this;
    }


    /**
     * @param $for
     * @param $with
     * @return $this
     */
    protected function removeContent($for, $with)
    {
        $tmp = [];

        foreach($this->get($for) as $value)
        {
            if(preg_match('=.*'.setcooki_regex_delimit($with).'.*=i', $value))
            {
                continue;
            }
            $tmp[] = $value;
        }
        $this->set($for, $tmp);
        return $this;
    }


    /**
     * @param $name
     * @param $attributes
     * @return string
     */
    protected function tag($name, $attributes)
    {
        $tag = sprintf("<%s", $name);
        foreach($attributes as $key => $val)
        {
            if(is_null($val))
            {
                $tag .= sprintf(' %s', $key);
            }else{
                $tag .= sprintf(' %s="%s"', $key, $val);
            }
        }
        $tag .= sprintf("%s>", ((setcooki_get_option(self::XHTML_STYLE, $this)) ? ' /' : ''));
        return $tag;
    }


    /**
     * @param $key
     * @param $content
     * @return string
     */
    protected function _render($key, $content)
    {
        if(is_array($content))
        {
            $t = '';
            if(in_array($key, array('meta', 'link')))
            {
                $t = str_repeat("\t", setcooki_get_option(self::HEAD_TABS, $this, 0));
            }
            $content = implode(sprintf("\r\n%s", $t), $content) . "\r\n";
        }
        return $content;
    }
}
