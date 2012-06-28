<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;
use \Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb;

/**
 * @group model
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 */
class BreadcrumbTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group model
     */
    public function testConstructor()
    {
        $bc = new Breadcrumb('foo', '/bar');
        $this->assertInstanceOf('\Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb', $bc);
    }

    /**
     * @group model
     */
    public function testGetters()
    {
        $bc = new Breadcrumb('foo', '/bar');
        $this->assertEquals('foo', $bc->label);
        $this->assertEquals('/bar', $bc->url);
    }


}
