<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;

class CartService
{
    const SESSION_FILE = 'session.json';

    public function __construct(private ProductRepository $productRepository)
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

        file_put_contents(self::SESSION_FILE, json_encode($cart, JSON_THROW_ON_ERROR));

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

        file_put_contents(self::SESSION_FILE, json_encode($cart, JSON_THROW_ON_ERROR));

        return $this->getCart();
    }

    public function clearCart(): array
    {
        file_put_contents(self::SESSION_FILE, json_encode([], JSON_THROW_ON_ERROR));

        return [];
    }

    private function getCart(): array
    {
        if (!file_exists(self::SESSION_FILE)) {
            file_put_contents(self::SESSION_FILE, json_encode([], JSON_THROW_ON_ERROR));
        }

        return json_decode(file_get_contents(self::SESSION_FILE), true, 512, JSON_THROW_ON_ERROR);
    }
}