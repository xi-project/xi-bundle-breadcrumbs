<?php

namespace Xi\Bundle\BreadcrumbsBundle\Twig\Extension;

use Twig_Extension;
use Twig_Function_Method;
use \Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Extends Twig to output breadcrumbs
 */
class BreadcrumbExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var BreadcrumbService
     */
    protected $service;

    /**
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->service = $container->get("xi_breadcrumbs");
    }

    /**
     * {$inheritDoc}
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            "xi_breadcrumbs" => new Twig_Function_Method(
                $this, "renderBreadcrumbs", array("is_safe" => array("html"))
            )
        );
    }

    /**
     * Returns the rendered breadcrumbs
     *
     * @return string
     */
    public function renderBreadcrumbs()
    {
        $router = $this->container->get('router');
        $request = $this->container->get('request');

        $route = $request->get('_route');
        try {
            $params = $router->match(rawurldecode($request->getPathInfo()));
        }
        catch (MethodNotAllowedException $e) {
            return;
        }

        return $this->container->get("templating")->render(
            "XiBreadcrumbsBundle:Default:breadcrumbs.html.twig",
            array( 'breadcrumbs' => $this->service->getBreadcrumbs((string)$route, $params))
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'breadcrumbs';
    }
}
