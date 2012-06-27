<?php

namespace Xi\Bundle\BreadcrumbsBundle\Model;

/**
 * A simple class to hold a single breadcrumb.
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Breadcrumb
{
    /**
     * @var string Label of the breadcrumb
     */
    private $label;

    /**
     * @var string Url of the breadcrumb
     */
    private $url;

    /**
     * @param string $label Label of the breadcrumb
     * @param string $url Url of the breadcrumb
     */
    public function __construct($label, $url = '')
    {
        $this->label = $label;
        $this->url = $url;
    }

    /**
     * @return string Label of the breadcrumb
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return string Url of the breadcrumb
     */
    public function getUrl() {
        return $this->url;
    }

    public function __toString()
    {
        $tpl = " Object\n(\n    label => %s\n    url => %s\n)";
        return __CLASS__ . sprintf($tpl, $this->getLabel(), $this->getUrl());
    }
}