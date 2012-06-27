<?php

namespace Xi\Bundle\BreadcrumbsBundle\Service;

use \InvalidArgumentException;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use \Symfony\Component\Routing\Route;
use \Symfony\Component\Routing\RouteCollection;
use \Symfony\Component\Routing\RouterInterface;

/**
 * A service class to build breadcrumbs from Symfony routes.
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $route = $this->getRoute($name);
        $parents = $this->getParents($name);

        if ($route && $parents) {
            foreach (
                array_merge($parents, array($name)) as $n
            ) {
                $breadcrumbs[] = $this->getLabel($n, $params);
            }
            return $breadcrumbs;
        } else {
            return array(); // fail quickly if not found
        }
    }

    /**
     * @param string $route
     * @return array
     */
    public function getParents($route)
    {
        $parents = array();
        $parent = $this->getParent($route);

        // Prevents circular loops by checking that the key doesn't exist already
        while ($parent && $parent !== $route && !array_key_exists($parent, $parents)) {
            $parents[$parent] = count($parents);
            $parent = $this->getParent($parent);
        }

        return array_reverse(array_flip($parents));
    }

    /**
     * @param string $name
     * @return Route|null
     */
    private function getParent($name) {
        $route = $this->getRoute($name);
        if ($route && $route->hasDefault('parent')) {
            return $route->getDefault('parent');
        } else {
            return null;
        }
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
     * @param string name
     * @return string
     */
    public function getUrl($name, array $params = array()) {
        return $this->router->generate($name, $this->matchParams($name, $params));
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

            // Check for routes without placeholder parameters
            if (!preg_match(self::TWIG_TAG, $route->getPattern())) {
                return array();
            }

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
     * @return Route|null
     */
    private function getRoute($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(__FUNCTION__ . '() only accepts route name as a string.');
        }
        return $this->getRouter()->getRouteCollection()->get($name);
    }
}
