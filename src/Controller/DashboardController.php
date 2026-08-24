<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ProductRepository $productRepository): Response
    {

        $products = $productRepository->findAll();
        $productc = count($products);

        return $this->render('dashboard/index.html.twig', [
            'products' => $products,
            'products_count' => $productc
        ]);
    }
}
