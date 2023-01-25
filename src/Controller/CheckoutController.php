<?php

namespace App\Controller;

use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    #[Route("/", name: "index")]
    public function index(ProductCategoryRepository $productCategoryRepository, CartService $cartService): Response
    {
        return $this->render('index.html.twig', [
            'productCategories' => $productCategoryRepository->findAll(),
            'cart' => $cartService->getReadableCart(),
        ]);
    }
    #[Route("/add/{productId}", name: "add", methods: ['GET'])]
    public function add(int $productId, ProductRepository $productRepository, CartService $cartService): Response
    {
        $cartService->addToCart($productRepository->findOneBy(['id' => $productId]));

        return $this->redirectToRoute('index');
    }

    #[Route("/remove/{productId}", name: "remove", methods: ['GET'])]
    public function remove(int $productId, ProductRepository $productRepository, CartService $cartService): Response
    {
        $cartService->removeFromCart($productRepository->findOneBy(['id' => $productId]));

        return $this->redirectToRoute('index');
    }

    #[Route("/restart", name: "restart", methods: ['GET'])]
    public function restart(RequestStack $requestStack): Response
    {
        $requestStack->getSession()->clear();

        return $this->redirectToRoute('index');
    }
}