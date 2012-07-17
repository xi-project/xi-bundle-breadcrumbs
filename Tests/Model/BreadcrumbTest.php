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
     * @test
     * @group model
     */
    public function constructor()
    {
        $bc = new Breadcrumb('foo', '/bar');
        $this->assertInstanceOf('\Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb', $bc);
    }

    /**
     * @test
     * @group model
     */
    public function getters()
    {
        $bc = new Breadcrumb('foo', '/bar');
        $this->assertEquals('foo', $bc->label);
        $this->assertEquals('/bar', $bc->url);
    }


}
