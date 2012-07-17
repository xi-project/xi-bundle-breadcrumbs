<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Xi\Bundle\BreadcrumbsBundle\EventListener\BreadcrumbListener;
use Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb;
use Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbService;

/**
 * @group events
 */
class BreadcrumbListenerTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->listener = new BreadcrumbListener($this->container);
        $this->service = new BreadcrumbService($this->container);

        $this->resolver = new ControllerResolver(
            $this->container,
            new ControllerNameParser(static::createKernel())
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->client = null;
        $this->container = null;
        $this->listener = null;
        $this->service = null;
        $this->resolver = null;
    }

    /**
     * @test
     * @group events
     */
    public function testOnKernelControllerEvent()
    {
        $this->client->request('GET', '/hello/Peter/do/play');

        $request = $this->client->getRequest();
        $controller = $this->resolver->getController($request);

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getController', 'getRequest'))
            ->getMock();
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
