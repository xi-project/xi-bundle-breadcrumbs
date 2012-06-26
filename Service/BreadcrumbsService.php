<?php

namespace Xi\Bundle\BreadcrumbsBundle\Service;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use \Symfony\Component\Routing\Route;
use \Symfony\Component\Routing\RouteCollection;
use \Symfony\Component\Routing\RouterInterface;

/**
 * A service class to build breadcrumbs from Symfony routes.
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 */
class BreadcrumbsService
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RouterInterface
     */
    protected $router;

    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->logger = $container->get('logger');

    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter(RouterInterface $router)
    {
        return $this->router = $router;
    }

    /**
     * @param string $route
     *
     * @return array
     */
    public function getBreadcrumbs($route, RouteCollection $rc = null, array $params = array())
    {
        if ("string" === gettype($route)) {
            $url = $this->getUri($route, $params);
            $this->logger->debug(
                "The URL for '${route}' is ${url}."
            );

            $route = $this->getRouteByName($route, $rc);
        }

        if ($route && $route->hasDefault('label') && $route->hasDefault('parent')) {
            return array_merge(
                (array) $this->getParents($route),
                (array) $route->getDefault('label')
            );
        } else {
            if ($route->hasDefault('parent')) {
                $this->logger->warn(
                    'Please specify both label and parent for route: ' . $route->getPattern()
                );
            }
            return array();
        }
    }

    /**
     * @param Route|null $route
     *
     * @return array
     */
    public function getParents($route)
    {
        if ($route && $route->hasDefault('parent')) {
            $parent = $route->getDefault('parent');
            return array_merge(
                (array) $this->getParents($this->getRouteByName($parent)),
                (array) $parent
            );
        } else {
            return array();
        }
    }

    private function getUri($route, array $params = array()) {
        return $this->router->generate($route, $params);
    }

    private function getRouteByName($name, RouteCollection $rc = null)
    {
        if (!$rc) { $rc = $this->getRoutes(); }

        $route = $rc->get($name) or array();
        return $route;
    }

    private function getRoutes()
    {
        return $this->getRouter()->getRouteCollection();
    }

}
