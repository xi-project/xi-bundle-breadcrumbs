<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;
use \ArrayIterator;
use \MultipleIterator;
use \Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb;
use \Xi\Bundle\BreadcrumbsBundle\Model\Trail;

/**
 * @group model
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 */
class TrailTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group model
     */
    public function testConstructor()
    {
        $trail = new Trail();
        $this->assertInstanceOf('\Xi\Bundle\BreadcrumbsBundle\Model\Trail', $trail);
    }

    /**
     * @group model
     */
    public function testInstanceVars()
    {
        $one = new Breadcrumb('one', '/1');
        $two = new Breadcrumb('two', '/2');
        $three = new Breadcrumb('three', '/3');

        $exp = array($one, $two, $three);
        $trail = new Trail($one, $two, $three);

        $this->assertEqualIterables($exp, iterator_to_array($trail));
    }


    /**
     * @group model
     */
    public function testIteration()
    {
        $one = new Breadcrumb('one', '/1');
        $two = new Breadcrumb('two', '/2');
        $three = new Breadcrumb('three', '/3');

        $exp = array($one, $two, $three);
        $trail = new Trail($one, $two, $three);

        foreach ($trail as $key => $breadcrumb) {
            $this->assertEquals($exp[$key], $breadcrumb);
        }
    }

    /**
     * @group model
     */
    public function testArrayAccess()
    {
        $one = new Breadcrumb('one', '/1');
        $two = new Breadcrumb('two', '/2');
        $three = new Breadcrumb('three', '/3');

        $exp = array($one, $two, $three);
        $trail = new Trail();

        $trail[99] = $three; // set
        $trail[2] = $trail[99]; // get
        $trail[1] = $two;
        $trail[0] = $one;
        unset($trail[99]); // unset

        $this->assertTrue(isset($trail[2])); // exists
        $this->assertCount(3, $trail);

        for ($i = 0; $i < count($trail); $i++) {
            $this->assertEquals($exp[$i], $trail[$i]);
        }
    }

    private function assertEqualIterables($expected, $actual) {
        $msg = "\nIterator %s differ.\nExpected: %s\nActual: %s";

        if (count($expected) !== count($actual)) {
            $this->fail(sprintf($msg, 'counts', count($expected), count($actual)));
        }

        $multiple = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);
        $multiple->attachIterator(new ArrayIterator($expected));
        $multiple->attachIterator(new ArrayIterator($actual));

        foreach ($multiple as $values) {
            $keys = $multiple->key();

            if ($keys[0] !== $keys[1]) {
                $this->fail(sprintf($msg, 'keys', strval($keys[0]), strval($keys[1])));
            }

            if ($values[0] !== $values[1]) {
                $this->fail(sprintf($msg, 'values', strval($values[0]), strval($values[1])));
            }
        }

        return true;
    }
}
