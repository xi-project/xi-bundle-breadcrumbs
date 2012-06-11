<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\Service;

use \Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use \Symfony\Component\EventDispatcher\Event;
use \Symfony\Component\DomCrawler\Crawler;
use \Xi\Bundle\BreadcrumbsBundle\Tests\ContainerTestCase; // @TODO how to reuse and not copy here?
use \Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbsService;


/**
 * @group service
 */
class BreadcrumbsServiceTest extends ContainerTestCase
{
    /**
     * @var BreadcrumbsService
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = new BreadcrumbsService($this->getContainer());
    }

    /**
     * @test
     * @group service
     */
    public function testConstructor() {
        $this->assertInstanceOf('Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbsService', new BreadcrumbsService($this->getContainer()));
    }

    /**
     * @test
     * @group service
     */
    public function testGetServiceFromContainer() {
        $this->assertInstanceOf('Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbsService', $this->getContainer()->get('xi_breadcrumbs'));
    }
}
