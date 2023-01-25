<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(private RequestStack $requestStack, private ProductRepository $productRepository)
    {
    }

    public function getReadableCart(): array
    {
        $cart = ['items'=>[]];
        $total = 0;

        foreach ($this->getCart() as $productId => $quantity) {
            $product = $this->productRepository->findOneBy(['id' => $productId]);

            $cart['items'][] = [
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'priceTotal' => $product->getPrice() * $quantity,
                'quantity' => $quantity,
            ];

            $total += ($product->getPrice() * $quantity);
        }

        $cart['total'] = $total;
        return $cart;
    }

    public function addToCart(Product $product): array
    {
        $cart = $this->getCart();
        $cart[$product->getId()] = isset($cart[$product->getId()]) ? $cart[$product->getId()] += 1 : 1;

        $this->requestStack->getSession()->set('cart', $cart);

        return $this->getCart();
    }
    public function removeFromCart(Product $product): array
    {
        $cart = $this->getCart();

        if (isset ($cart[$product->getId()])) {
            $cart[$product->getId()] -= 1;
            if ($cart[$product->getId()] < 1) {
                unset($cart[$product->getId()]);
            }
        }

        $this->requestStack->getSession()->set('cart', $cart);

        return $this->getCart();
    }

    private function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }
}