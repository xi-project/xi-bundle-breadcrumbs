<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\Service;

use \Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use \Symfony\Component\EventDispatcher\Event;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\DomCrawler\Crawler;
use \Symfony\Component\Routing\Loader\YamlFileLoader;
use \Symfony\Component\Routing\Route;
use \Symfony\Component\Routing\Router;
use \Symfony\Component\Routing\RouterInterface;
use \Symfony\Component\Routing\RouteCollection;
use \Xi\Bundle\BreadcrumbsBundle\Tests\ContainerTestCase; // @TODO how to reuse and not copy here?
use \Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbsService;


/**
 * @group service
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
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
        $this->assertInstanceOf(
            'Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbsService',
            new BreadcrumbsService($this->getContainer())
        );
    }

    /**
     * @test
     * @group service
     */
    public function testGetServiceFromContainer() {
        $this->assertInstanceOf(
            'Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbsService',
            $this->getContainer()->get('xi_breadcrumbs')
        );
    }

    /**
     * @test
     * @group service
     */
    public function testHasRouterAndRouteCollection()
    {
        $router = $this->service->getRouter();
        $this->assertInstanceOf('Symfony\Component\Routing\Router', $router);

        $rc = $router->getRouteCollection();
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $rc);
    }

    /**
     * @test
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithOnlyLabelOrParent()
    {
        $this->service->setRouter($this->loadRouter('only_label_or_parent.yml'));
        $this->assertEquals(array(), $this->service->getBreadcrumbs('only_label'));
        $this->assertEquals(array('labello', 'only_parent'), $this->service->getBreadcrumbs('only_parent'));
    }

    /**
     * @test
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithParent()
    {
        $this->service->setRouter($this->loadRouter('simple.yml'));

        $breadcrumbs = array('root', 'foo');
        $this->assertEquals($breadcrumbs, $this->service->getBreadcrumbs('foo'));
    }

    /**
     * @test
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithParams()
    {
        $this->service->setRouter($this->loadRouter('simple.yml'));

        $slug = 'b1-1';
        $breadcrumbs = array('root', 'foo', "bar ${slug}");

        $this->assertEquals(
            $breadcrumbs,
            $this->service->getBreadcrumbs(
                'bar',
                array('slug' => $slug)
            )
        );
    }

    /**
     * @test
     * @group service
     */
    public function testGetBreadcrumbsCircular()
    {
        $this->service->setRouter($this->loadRouter('circular.yml'));

        $this->assertEquals(array(), $this->service->getBreadcrumbs('loop'));
        $this->assertEquals(array(), $this->service->getParents('loop'));

        $this->assertEquals(array('flip'), $this->service->getParents('flop'));
        $this->assertEquals(array('flop'), $this->service->getParents('flip'));

        $parents = array('c', 'a', 'd', 'r');
        $this->assertEquals(array_slice($parents, 0, 3), $this->service->getParents('r'));
        $this->assertEquals($parents, $this->service->getBreadcrumbs('r'));

        $cycle = array('a', 'd', 'c', 'a', 'd');
        $this->assertEquals(array_slice($cycle, 0, 3), $this->service->getBreadcrumbs('c'));
        $this->assertEquals(array_slice($cycle, 1, 3), $this->service->getBreadcrumbs('a'));
        $this->assertEquals(array_slice($cycle, 2, 3), $this->service->getBreadcrumbs('d'));
    }

    /**
     * @test
     * @group service
     */
    public function testGetParents()
    {
        $router = $this->service->setRouter($this->loadRouter('parents.yml'));
        $this->assertInstanceOf('\Symfony\Component\Routing\Router', $router);

        $this->assertEquals(
            array('root', 'some'),
            $this->service->getParents('path')
        );

        $this->assertEquals(
            array('unrooted'),
            $this->service->getParents('way')
        );

        $this->assertEquals(array(), $this->service->getParents('root'));
        $this->assertEquals(array(), $this->service->getParents('notfound'));
    }

    /**
     * @test
     * @group service
     */
    public function testGetLabel()
    {
        $router = $this->service->setRouter($this->loadRouter('labels.yml'));
        $this->assertInstanceOf('\Symfony\Component\Routing\Router', $router);

        $rc = $router->getRouteCollection();

        $this->assertEquals('home', $this->service->getLabel('root'));
        $this->assertEquals('lussu', $this->service->getLabel('lussu'));
        $this->assertEquals('loso', $this->service->getLabel('loso'));
        $this->assertEquals('loso poski', $this->service->getLabel('loso', array('slug' => 'poski')));
        $this->assertEquals('musta tussi', $this->service->getLabel('tussi'));
        $this->assertEquals('Textmarker 880 tussi', $this->service->getLabel('tussi', array('model' => 'Textmarker 880')));
    }

    /**
     * @param string @yamlFile YAML routing configuration file name
     * @return RouteCollection
     */
    private function loadRouteCollection($yamlFile)
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        return $loader->load($yamlFile);
    }

    /**
     * @param string @yamlFile YAML routing configuration file name
     * @return Router
     */
    private function loadRouter($yamlFile)
    {
        $locator = new FileLocator(array(__DIR__.'/../Fixtures'));
        /* $requestContext = new RequestContext($_SERVER['REQUEST_URI']); */

        return new Router(
            new YamlFileLoader($locator),
            $yamlFile /*,
            array('cache_dir' => __DIR__.'/cache'),
            $requestContext */
        );
    }
}
