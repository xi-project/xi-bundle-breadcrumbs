<?php

namespace Xi\Bundle\BreadcrumbsBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;


/**
 * Listens to Kernel Controller events, and builds breadcrumbs from Symfony routes if needed.
 *
 * @author Peter Hillerström <peter.hillerstrom@soprano.fi>
 */
class BreadcrumbsListener extends Controller
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(/* ContainerInterface */ $container)
    {
        $this->container = $container;
    }

    public function onKernelController(/* FilterControllerEvent */ $event)
    {
        $logger = $this->container->get('logger');
        $logger->debug('Got kernel.controller event');

        /* $router = $this->container->get('router'); */
    }
}
