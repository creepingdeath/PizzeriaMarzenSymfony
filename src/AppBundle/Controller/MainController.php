<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MainController extends Controller {

    /**
     * @Route("/")
     */
    public function indexAction(Request $request) {
        $session = new Session();

        $products = new ProductController();

        return $this->render('default/index.html.twig', [
                    'login' => $session->get('login'),
                    'products' => $products->showProducts()
        ]);
    }

}
