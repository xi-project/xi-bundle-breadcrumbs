<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Xi\Bundle\BreadcrumbsBundle\EventListener\BreadcrumbListener;
use Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb;
use Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbService;

/**
 * @group events
 */
class BreadcrumbListenerTest extends WebTestCase
{
    /**
     * @var BreadcrumbService
     */
    protected $service;

    static protected $kernel;

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->listener = new BreadcrumbListener($this->container);
        $this->service = new BreadcrumbService($this->container);

        static::$kernel = static::createKernel();

        $this->resolver = new ControllerResolver(
            $this->container,
            new ControllerNameParser(static::$kernel)
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->listener = null;
        $this->service = null;
    }

    /**
     * @test
     * @group events
     */
    public function testOnKernelControllerEvent()
    {
        $request = Request::create('/hello/Peter/do/play', 'GET', array(
            'name' => 'Peter',
            'thing' => 'play'
        ));
        $controller = $this->resolver->getController($request);

        $event = $this->getMock('FilterControllerEvent', array('getController', 'getRequest'));
        $event->expects($this->any())->method('getController')->will($this->returnValue($controller));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($request));

        $this->listener->onKernelController($event);

        $this->assertEquals(
            array(
                'hello' => new Breadcrumb('hello Peter', '/hello/Peter'),
                'doing' => new Breadcrumb('doing play', '/hello/Peter/do/play')
            ),
            $this->service->getBreadcrumbs('doing', array(
                'name' => 'Peter',
                'thing' => 'play'
            ))
        );
    }
}
