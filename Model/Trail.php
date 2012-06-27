<?php

namespace Xi\Bundle\BreadcrumbsBundle\Model;

use \Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use \Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb;

/**
 * A Trail of breadcrumbs.
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Trail implements \Iterator, \ArrayAccess, \Countable
{
    /**
     * @var Breadcrumb[] Array of breadcrumbs
     */
    private $breadcrumbs;

    /**
     * @param Breadcrumb $arguments Variable length arguments list of Breadcrumbs
     */
    public function __construct()
    {
        $this->breadcrumbs = array();

        if (func_num_args()) {
            foreach (func_get_args() as $bc) {
                if ($bc instanceof Breadcrumb) { $this->breadcrumbs[] = $bc; }
            }
        }
    }

    /**
     * Add breadcrumb into Trail
     *
     * @param string $label
     * @param string $url
     */
    public function add($label, $url)
    {
        $this->breadcrumbs[] = new Breadcrumb($label, $url);
    }


    // IteratorInterface

    public function rewind()
    {
        return reset($this->breadcrumbs);
    }

    public function next()
    {
        return next($this->breadcrumbs);
    }

    public function valid()
    {
        return key($this->breadcrumbs) !== null;
    }

    public function current()
    {
        return current($this->breadcrumbs);
    }

    public function key()
    {
        return key($this->breadcrumbs);
    }


    // ArrayAccessInterface

    public function offsetExists($offset)
    {
        return isset($this->breadcrumbs[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        $this->breadcrumbs[$offset] = $value;
    }

    public function offsetGet($offset)
    {
        return isset($this->breadcrumbs[$offset]) ? $this->breadcrumbs[$offset] : null;
    }

    public function offsetUnset($offset)
    {
        unset($this->breadcrumbs[$offset]);
    }


    // CountableInterface

    public function count() {
        return count($this->breadcrumbs);
    }


    public function __toString()
    {
        return strval($this->breadcrumbs);
    }
}
