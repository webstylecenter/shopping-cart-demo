<?php

namespace App\Tests;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ShoppingCartTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = static::getContainer();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testDatabaseContents(): void
    {
        $products = $this->entityManager
            ->getRepository(Product::class)
            ->findAll();

        $this->assertCount(8, $products);
    }

    public function testTotalAmount(): void
    {
        $cartService = $this->prepareCart();

        // Add all products from our fixtured database
        foreach ($this->getProductRepository()->findAll() as $product) {
            $cartService->addToCart($product);
        }

        // Check to see if product total is correct
        $this->assertSame(89825, $cartService->getReadableCart()['total']);

        // Check if a certain product is listed
        $this->assertSame(
            [
                'name' => 'Symfony',
                'price' => 1000,
                'priceTotal' => 1000,
                'quantity' => 1,
            ],
            $cartService->getReadableCart()['items'][0]
        );
    }

    public function testDoubleProduct(): void
    {
        $cartService = $this->prepareCart();

        $webHostingProduct = $this->getProductRepository()->findOneBy(['name' => 'Webhosting']);
        $apiPlatformProduct = $this->getProductRepository()->findOneBy(['name' => 'ApiPlatform']);

        $this->assertNotEmpty($webHostingProduct);
        $this->assertNotEmpty($apiPlatformProduct);

        $cartService->addToCart($webHostingProduct);
        $cartService->addToCart($apiPlatformProduct);

        $this->assertSame(81250, $cartService->getReadableCart()['total']);

        $cartService->addToCart($apiPlatformProduct);
        $this->assertSame(131250, $cartService->getReadableCart()['total']);
        $this->assertSame($cartService->getReadableCart()['items'][0]['price'], $webHostingProduct->getPrice());
        $this->assertSame($cartService->getReadableCart()['items'][1]['price'], $apiPlatformProduct->getPrice());
        $this->assertSame($cartService->getReadableCart()['items'][1]['quantity'], 2);
        $this->assertSame($cartService->getReadableCart()['items'][1]['priceTotal'], $apiPlatformProduct->getPrice() * 2);
    }

    public function testRemovalOfProduct(): void
    {
        $cartService = $this->prepareCart();

        // Load product
        $webHostingProduct = $this->getProductRepository()->findOneBy(['name' => 'Webhosting']);
        $apiPlatformProduct = $this->getProductRepository()->findOneBy(['name' => 'ApiPlatform']);

        $this->assertNotEmpty($webHostingProduct);
        $this->assertNotEmpty($apiPlatformProduct);

        // Add products
        $cartService->addToCart($webHostingProduct);
        $cartService->addToCart($apiPlatformProduct);
        $cartService->addToCart($apiPlatformProduct);

        $this->assertSame(131250, $cartService->getReadableCart()['total']);

        // Remove 1 product that is added twice
        $cartService->removeFromCart($apiPlatformProduct);

        // Test if product still exists but is only listed once
        $this->assertSame($cartService->getReadableCart()['items'][1]['quantity'], 1);
        $this->assertSame($cartService->getReadableCart()['items'][1]['priceTotal'], $apiPlatformProduct->getPrice());
        $this->assertSame(81250, $cartService->getReadableCart()['total']);

        // Remove another product, it should no longer be listed
        $cartService->removeFromCart($webHostingProduct);
        $this->assertCount(1, $cartService->getReadableCart()['items']);
        $this->assertSame(50000, $cartService->getReadableCart()['total']);

        // Make sure you don't less than zero products
        $cartService->removeFromCart($webHostingProduct);
        $this->assertCount(1, $cartService->getReadableCart()['items']);
        $this->assertSame(50000, $cartService->getReadableCart()['total']);
    }

    private function prepareCart(): CartService
    {
        $cartService = $this->getCartService();
        $cartService->clearCart();

        // Make sure the cart is empty before our test
        $this->assertSame($cartService->getReadableCart(), [
            'items' => [],
            'total' => 0
        ]);

        return $cartService;
    }

    private function getCartService(): CartService
    {
        return $this->container->get(CartService::class);
    }

    private function getProductRepository(): ProductRepository
    {
        return $this->container->get(ProductRepository::class);
    }
}
