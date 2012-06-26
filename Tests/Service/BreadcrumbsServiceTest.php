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
    public function testGetBreadcrumbsForRouteWithoutParent()
    {
        $route = new Route('/home', array('label' => 'ignored'));
        $this->assertEquals(array(), $this->service->getBreadcrumbs($route));
    }

    /**
     * @test
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithoutLabel()
    {
        $route = new Route('/home', array('parent' => 'ignored'));
        $this->assertEquals(array(), $this->service->getBreadcrumbs($route));
    }

    /**
     * @test
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithParent()
    {
        $this->service->setRouter($this->loadRouter('oneparent.yml'));

        $breadcrumbs = array('root', 'foo');
        $this->assertEquals($breadcrumbs, $this->service->getBreadcrumbs('foo'));
    }

    /**
     * @test
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithParams()
    {
        $this->service->setRouter($this->loadRouter('oneparent.yml'));

        $breadcrumbs = array('root', 'foo', 'bar {slug}');

        $this->assertEquals(
            $breadcrumbs,
            $this->service->getBreadcrumbs(
                'bar',
                null,
                array('slug' => 'b1-1')
            )
        );
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
