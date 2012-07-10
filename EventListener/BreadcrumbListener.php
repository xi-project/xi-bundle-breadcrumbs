<?php

namespace Xi\Bundle\BreadcrumbsBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;


/**
 * Listens to Kernel Controller events, and builds breadcrumbs from Symfony routes if needed.
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 */
class BreadcrumbListener extends Controller
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
        $this->service = $this->container->get('xi_breadcrumbs');
        $this->router = $this->container->get('router');
    }

    public function onKernelController(/* FilterControllerEvent */ $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $controller = $event->getController();
        $request = $event->getRequest();

        $route = $request->get('_route');
        $params = $this->router->match($request->getRequestUri());

        $this->service->addBreadcrumbs($route, $params);
    }
}
