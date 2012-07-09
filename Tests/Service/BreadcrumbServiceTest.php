<?php

namespace Xi\Bundle\BreadcrumbsBundle\Tests\Service;

use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\Routing\Loader\YamlFileLoader;
use \Symfony\Component\Routing\Route;
use \Symfony\Component\Routing\RouteCollection;
use \Symfony\Component\Routing\Router;
use \Symfony\Component\Routing\RouterInterface;
use \Xi\Bundle\BreadcrumbsBundle\Model\Breadcrumb;
use \Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbService;
use \Xi\Bundle\BreadcrumbsBundle\Tests\ContainerTestCase;

/**
 * @group service
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 */
class BreadcrumbServiceTest extends ContainerTestCase
{
    /**
     * @var BreadcrumbService
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = new BreadcrumbService($this->getContainer());
    }

    /**
     * @test
     * @group service
     */
    public function testConstructor() {
        $this->assertInstanceOf(
            'Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbService',
            new BreadcrumbService($this->getContainer())
        );
    }

    /**
     * @test
     * @group service
     */
    public function testGetServiceFromContainer() {
        $this->assertInstanceOf(
            'Xi\Bundle\BreadcrumbsBundle\Service\BreadcrumbService',
            $this->getContainer()->get('xi_breadcrumbs')
        );
    }

    /**
     * @test
     * @group service
     * @group intlrouting
     */
    public function testRouter()
    {
        $router = $this->service->getRouter();
        $this->assertArrayHasKey('Symfony\Component\Routing\RouterInterface', class_implements($router));

        $router = $this->useRouter('simple.yml');
        $this->assertArrayHasKey('Symfony\Component\Routing\RouterInterface', class_implements($router));
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithOnlyLabelOrParent()
    {
        $this->useRouter('only_label_or_parent.yml');

        $this->assertEquals(
            array(),
            $this->service->getBreadcrumbs('only_label')
        );

        $this->assertEquals(
            array(
                'only_label' => new Breadcrumb('labello', '/'),
                'only_parent' => new Breadcrumb('only_parent', '/child')
            ),
            $this->service->getBreadcrumbs('only_parent')
        );
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithParams()
    {
        $this->useRouter('simple.yml');

        $slug = 'b1-1';
        $breadcrumbs = array(
            'root' => new Breadcrumb('root', '/'),
            'foo' => new Breadcrumb('foo', '/foo'),
            'bar' => new Breadcrumb("bar ${slug}", "/foo/bar/${slug}")
        );

        $this->assertEquals(
            array_slice($breadcrumbs, 0, 2),
            $this->service->getBreadcrumbs('foo')
        );

        $this->assertEquals(
            $breadcrumbs,
            $this->service->getBreadcrumbs('bar', array('slug' => $slug))
        );
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     */
    public function testGetBreadcrumbsForRoutesWithPrefix()
    {
        $this->useRouter('prefix.yml');

        $slug = 'b-78';
        $breadcrumbs = array(
            'home' => new Breadcrumb('home', '/home'),
            'root' => new Breadcrumb('root', '/prefix/'),
            'foo' => new Breadcrumb('foo', '/prefix/foo'),
            'bar' => new Breadcrumb("bar ${slug}", "/prefix/foo/bar/${slug}")
        );

        $this->assertEquals(
            $breadcrumbs,
            $this->service->getBreadcrumbs('bar', array('slug' => $slug))
        );
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     */
    public function testGetBreadcrumbsForRouteWithoutRequirements()
    {
        $this->useRouter('no_requirements.yml');

        $name = 'Peter';
        $thing = 'code';

        $bc = array(
            'hello' => new Breadcrumb("hello ${name}", "/hello/${name}"),
            'doing' => new Breadcrumb("doing ${thing}", "/hello/Peter/do/${thing}")
        );

        $this->assertEquals($bc, $this->service->getBreadcrumbs('doing', array(
            'name' => $name,
            'thing' => $thing
        )));
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     */
    public function testGetBreadcrumbsCircular()
    {
        $this->useRouter('circular.yml');

        $this->assertEquals(array(), $this->service->getBreadcrumbs('loop'));
        $this->assertEquals(array(), $this->service->getParents('loop'));

        $this->assertEquals(array('flip'), $this->service->getParents('flop'));
        $this->assertEquals(array('flop'), $this->service->getParents('flip'));

        $parents = array('c', 'a', 'd', 'r');
        $this->assertEquals(array_slice($parents, 0, 3), $this->service->getParents('r'));

        $cycle = array(
            'a' => new Breadcrumb('a', '/a'),
            'd' => new Breadcrumb('d', '/d'),
            'c' => new Breadcrumb('c', '/c'),
            'r' => new Breadcrumb('r', '/r')
        );
        $this->assertEquals(
            $this->array_get($cycle, array('c', 'd', 'a')),
            $this->service->getBreadcrumbs('c')
        );
        $this->assertEquals(
            $this->array_get($cycle, array('a', 'c', 'd')),
            $this->service->getBreadcrumbs('a')
        );
        $this->assertEquals(
            $this->array_get($cycle, array('d', 'a', 'c')),
            $this->service->getBreadcrumbs('d')
        );
        $this->assertEquals(
            $this->array_get($cycle, array('d', 'a', 'c', 'r')),
            $this->service->getBreadcrumbs('r')
        );
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     * @group intlrouting
     */
    public function testGetBreadcrumbsBeSimpleIntl()
    {
        if (!($this->getContainer()->get('router') instanceof \BeSimple\I18nRoutingBundle\Routing\Router)) {
            $this->markTestSkipped('BeSimpleI18nRoutingBundle not loaded.');
        }

        $router = $this->getMock('BesimpleI18nRouter', array('generate', 'getRouteCollection'));
        $router->expects($this->any())
            ->method('getRouteCollection')
            ->will($this->returnValue($this->getBeSimpleRouteFixture()));

        $map = array(
            array('home.en', array(), '/home'),
            array('home.fi', array(), '/koti'),
            array('one.en', array(), '/one'),
            array('one.fi', array(), '/yksi'),
            array('two.en', array('_locale' => 'en', 'slug' => 3), '/one/two/3'),
            array('two.fi', array('_locale' => 'fi', 'slug' => 3), '/yksi/kaksi/3')
        );
        $router->expects($this->any())->method('generate')->will($this->returnValueMap($map));

        $this->service->setRouter($router);

        // Expected values

        $slug = 3;

        $en = array(
            'home.en' => new Breadcrumb('home', '/home'),
            'one.en' => new Breadcrumb('one', '/one'),
            'two.en' => new Breadcrumb("two ${slug}", "/one/two/${slug}"),
        );

        $fi = array(
            'home.fi' => new Breadcrumb('home', '/koti'),
            'one.fi' => new Breadcrumb('one', '/yksi'),
            'two.fi' => new Breadcrumb("two ${slug}", "/yksi/kaksi/${slug}"),
        );

        // Asserts

        $this->assertEquals($en, $this->service->getBreadcrumbs('two', array(
            '_locale' => 'en',
            'slug' => $slug
        )));

        $this->assertEquals($fi, $this->service->getBreadcrumbs('two', array(
            '_locale' => 'fi',
            'slug' => $slug
        )));
    }

    private function getBeSimpleRouteFixture() {
        $rc = new RouteCollection();

        // EN routes
        $rc->add('home.en', new Route('/home', array(
            '_locale' => 'en',
            'label' => 'home'
        )));
        $rc->add('one.en', new Route('/one', array(
            '_locale' => 'en',
            'label' => 'one',
            'parent' => 'home',
        )));
        $rc->add('two.en', new Route('/one/two/{slug}', array(
            '_locale' => 'en',
            'label' => 'two {slug}',
            'parent' => 'one',
            'requirements' => array('slug' => "\d+")
        )));

        // FI routes
        $rc->add('home.fi', new Route('/koti', array(
            '_locale' => 'fi',
            'label' => 'home'
        )));
        $rc->add('one.fi', new Route('/yksi', array(
            '_locale' => 'fi',
            'label' => 'one',
            'parent' => 'home'
        )));
        $rc->add('two.fi', new Route('/yksi/kaksi/{slug}', array(
            '_locale' => 'fi',
            'label' => 'two {slug}',
            'parent' => 'one',
            'requirements' => array('slug' => "\d+")
        )));
        return $rc;
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     */
    public function testGetParents()
    {
        $router = $this->useRouter('parents.yml');

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
     * @depends testRouter
     * @group service
     */
    public function testGetLabel()
    {
        $router = $this->useRouter('labels.yml');

        $this->assertEquals('home', $this->service->getLabel('root'));
        $this->assertEquals('lussu', $this->service->getLabel('lussu'));
        $this->assertEquals('loso', $this->service->getLabel('loso'));
        $this->assertEquals('loso poski', $this->service->getLabel('loso', array('slug' => 'poski')));
        $this->assertEquals('musta tussi', $this->service->getLabel('tussi'));
        $this->assertEquals('Textmarker 880 tussi', $this->service->getLabel('tussi', array('model' => 'Textmarker 880')));
    }

    /**
     * @test
     * @depends testRouter
     * @group service
     */
    public function testGetUrl()
    {
        $router = $this->useRouter('simple.yml');

        $slug = 'd-12'; $lus = '3';
        $this->assertEquals('/', $this->service->getUrl('root'));
        $this->assertEquals('/foo', $this->service->getUrl('foo'));
        $this->assertEquals("/foo/bar/${slug}", $this->service->getUrl('bar', array('slug' => $slug)));
        $this->assertEquals(
            "/foo/bar/${slug}/${lus}",
            $this->service->getUrl('quuz', array('slug' => $slug, 'lus' => $lus))
        );
    }

    /**
     * Sets the router used in the BreadcrumbService with a YAML config
     *
     * @param string @yamlFile YAML routing configuration file name
     * @return Router
     */
    private function useRouter($yamlFile)
    {
        $locator = new FileLocator(array(__DIR__.'/../Fixtures'));
        $router = new Router(new YamlFileLoader($locator), $yamlFile);

        return $this->service->setRouter($router);
    }

    private function array_get(array $array, array $indices) {
        $out = array();
        foreach ($indices as $i) {
            array_key_exists($i, $array) ? $out[$i] = $array[$i] : null;
        }
        return $out;
    }

}
