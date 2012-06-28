<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\Service;

use \Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use \Symfony\Component\EventDispatcher\Event;
use \Symfony\Component\DomCrawler\Crawler;
use \Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbsService;
use \Xi\Bundle\BreadcrumbsBundle\EventListener\BreadcrumbsListener;


/**
 * @group events
 */
class BreadcrumbsServiceEventTest extends WebTestCase
{
    /**
     * @var BreadcrumbsService
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->service = new BreadcrumbsService($this->container);
        $this->listener = new BreadcrumbsListener($this->container);
    }

    /**
     * @test
     * @group events
     */
    public function testOnKernelControllerEvent() {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('kernel.controller', array($this->listener, 'onKernelController'));

        //$crawler = $this->client->request('GET', '/hello/Peter');

        $dispatcher->dispatch('kernel.controller',
            new Event($this->listener, 'xyz')
        );

    }
}
