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
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 */
class ContainerTestCase extends PHPUnit_Framework_Testcase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Kernel
     */
    private $kernel;

    public function setUp()
    {
        parent::setUp();

        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }
}
