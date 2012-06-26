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
    const TWIG_TAG = "# ?\{([^/}]+)\} ?#";

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
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
    public function getBreadcrumbs($name, array $params = array())
    {
        if (!$route = $this->getRoute($name)) {
            return array(); // fail quickly if not found
        }

        if ($route && $route->hasDefault('parent')) {
            return array_merge(
                (array) $this->getParents($route, $params),
                (array) $this->getLabel($name, $params)
            );
        } else {
            return array();
        }
    }

    /**
     * @param Route|null $route
     *
     * @return array
     */
    public function getParents($route, array $params = array())
    {
        if ($route && $route->hasDefault('parent') &&
            $parent = $route->getDefault('parent')
        ) {
            return array_merge(
                (array) $this->getParents($this->getRoute($parent), $params),
                (array) $this->getLabel($parent, $params)
            );
        }
        return array();
    }

    /**
     * @param string name
     * @return string
     */
    public function getLabel($name, array $params = array())
    {
        if ($route = $this->getRoute($name)) {
            if ($route->hasDefault('label')) {
                return $this->applyParams(
                    $route->getDefault('label'),
                    $this->matchParams($name, $params, true)
                );
            } else {
                return $name;
            }
        } else {
            return '';
        }
    }

    /**
     * @param string str
     * @return string
     */
    protected function applyParams($str, array $params)
    {
        $patterns = array_map(
            function ($tag) {
                return "/\{${tag}\}/";
            },
            array_keys($params)
        );
        $res = preg_replace($patterns, array_values($params), $str);
        return preg_replace(self::TWIG_TAG, '', $res); // cleanup missing params
    }

    /**
     * Returns only parameters applicable for the named route/label
     * @param string name
     * @return array
     */
    private function matchParams($name, array $params, $fromLabel = false)
    {
        if ($route = $this->getRoute($name)) {

            $reqs = $route->getRequirements();

            // Get default values for missing parameters
            foreach ($route->getDefaults() as $def => $value) {
                if (!array_key_exists($def, $params) && array_key_exists($def, $reqs)) {
                    $params[$def] = $value;
                }
            }

            if (!empty($params) && $reqs) {
                return array_intersect_key($params, $reqs);
            } else {
                $template = $fromLabel
                    ? $route->getDefault('label')
                    : $route->getPattern();
                return preg_split(self::TWIG_TAG, $template);
            }

        } else {
            return array();
        }
    }

    /**
     * @param string name
     * @return string
     */
    private function getUri($name, array $params = array()) {
        return $this->router->generate($name, $this->matchParams($name, $params));
    }

    /**
     * @param string name
     * @return Route|null
     */
    private function getRoute($name)
    {
        return $this->getRouter()->getRouteCollection()->get($name);
    }
}
