<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Xi\Bundle\BreadcrumbsBundle\Controller\DefaultController;
use Xi\Bundle\BreadcrumbsBundle\EventListener\BreadcrumbListener;
use Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb;
use Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbService;

/**
 * @group events
 */
class BreadcrumbServiceEventTest extends WebTestCase
{
    /**
     * @var BreadcrumbService
     */
    protected $service;

    static protected $kernel;

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        $this->dispatcher = new EventDispatcher();
        $this->listener = new BreadcrumbListener($this->container);
        $this->service = new BreadcrumbService($this->container);

        static::$kernel = static::createKernel();

        $this->resolver = new ControllerResolver(
            $this->container,
            new ControllerNameParser(static::$kernel)
        );

    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->dispatcher = null;
        $this->listener = null;
        $this->service = null;
    }

    /**
     * @test
     * @group events
     */
    public function testOnKernelControllerEvent()
    {
        $this->dispatcher->addListener('kernel.controller', array($this->listener, 'onKernelController'));

        $crawler = $this->client->request('GET', '/hello/Peter/do/play');
        $request = $this->client->getRequest();
        $controller = $this->resolver->getController($request);

        $fcEvent = new FilterControllerEvent(
            $this->getKernel(),
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->dispatcher->dispatch('kernel.controller', $fcEvent);

        $bc = array(
            'hello' => new Breadcrumb('hello Peter', '/hello/Peter'),
            'doing' => new Breadcrumb('doing play', '/hello/Peter/do/play')
        );

        $this->assertEquals($bc, $this->service->getBreadcrumbs('doing', array(
            'name' => 'Peter',
            'thing' => 'play'
        )));
    }

    private function getKernel()
    {
        return static::$kernel;
    }
}
