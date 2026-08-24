<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product', name: 'app_product_')]
class ProductController extends AbstractController
{
    private function getUploadDirectory(): string
    {
        return $this->getParameter('kernel.project_dir') . '/public/image';
    }

    #[Route('/', name: 'index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('imageFiles')->getData();
            if ($imageFiles) {
                $fileNames = $this->uploadFiles($imageFiles, $slugger);
                $product->setImages($fileNames);
            }

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('product/new.html.twig', [
            'productForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('imageFiles')->getData();
            if ($imageFiles && count($imageFiles) > 0) {
                $this->deleteExistingFiles($product->getImages());
                $fileNames = $this->uploadFiles($imageFiles, $slugger);
                $product->setImages($fileNames);
            }

            $em->flush();

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('product/edit.html.twig', [
            'productForm' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete-product-' . $product->getId(), $request->request->get('_token'))) {
            $this->deleteExistingFiles($product->getImages());
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('app_dashboard');
    }

    /**
     * @param UploadedFile[] $files
     * @return string[]
     */
    private function uploadFiles(array $files, SluggerInterface $slugger): array
    {
        $names = [];
        foreach ($files as $file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid(), $file->guessExtension());

            $file->move($this->getUploadDirectory(), $newFilename);
            $names[] = $newFilename;
        }

        return $names;
    }

    /**
     * @param string[] $fileNames
     */
    private function deleteExistingFiles(array $fileNames): void
    {
        foreach ($fileNames as $fileName) {
            if (!$fileName) {
                continue;
            }

            $filePath = $this->getUploadDirectory() . '/' . $fileName;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
