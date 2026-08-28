<?php

namespace App\Controller;

use App\Entity\Product;
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
        $page = $request->query->getInt('page', 1);
        $search = $request->query->getString('search');
        $product = $prodrepository->paginatoreproductclientSearch($page, $search);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $product,
            'search' => $search,
        ]);
    }

    #[Route('/gallery/{id}', name: 'app_gallery', methods: ['GET'])]
    public function gallery(Product $product): Response
    {
        return $this->render('gallery/show.html.twig', [
            'product' => $product,
        ]);
    }
}
