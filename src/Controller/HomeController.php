<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $prodrepository, Request $request): Response
    {
        $page= $request->query->getInt('page',1);
        $product = $prodrepository->paginatoreproductclient($page);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $product,
        ]);
    }
}
