<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {

        $page = $request -> query->getInt('page', 1);
        $products = $productRepository->paginatoreproduct($page);
        $comptes = $productRepository->findAll();
        $product = count($comptes);

        return $this->render('dashboard/index.html.twig', [
            'products' => $products,
            'products_count' => $product
        ]);
    }
}
