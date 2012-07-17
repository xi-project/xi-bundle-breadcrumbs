<?php

namespace Xi\Bundle\BreadcrumbsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('XiBreadcrumbsBundle:Default:index.html.twig', array('name' => $name));
    }
}
