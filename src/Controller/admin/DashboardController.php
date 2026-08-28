<?php

namespace App\Controller\admin;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {

        $page = $request -> query->getInt('page', 1);
        $products = $productRepository->paginatoreproduct($page);
        $product = $productRepository->countProducts();

        return $this->render('dashboard/admin/index.html.twig', [
            'products' => $products,
            'products_count' => $product
        ]);
    }
}
