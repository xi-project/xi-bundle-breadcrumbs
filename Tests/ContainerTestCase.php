<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests;

use PHPUnit_Framework_TestCase,
    AppKernel,
    Symfony\Component\DependencyInjection\Container;

require_once($_SERVER['KERNEL_DIR'] . "/AppKernel.php");

/**
 * A base class which initializes service container.
 *
 * Can be used for functional testing when you don't want to mock all the
 * dependencies.
 *
 * @author Mikko Hirvonen <mikko.hirvonen@soprano.fi>
 */
class ContainerTestCase extends PHPUnit_Framework_Testcase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $kernel = new AppKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
