<?php

namespace Fedmarket\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('FedmarketWebBundle:Default:index.html.twig', array('name' => $name));
    }
}
