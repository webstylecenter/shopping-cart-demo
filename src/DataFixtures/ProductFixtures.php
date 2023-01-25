<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Repository\ProductCategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private ProductCategoryRepository $productCategoryRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->productCategoryRepository->findAll() as $productCategory) {
            $productCategories[$productCategory->getName()] = $productCategory;
        }

        $products = [
            'Symfony' => [
                'category' => $productCategories['website'],
                'price' => 1000
            ],
            'Laravel' => [
                'category' => $productCategories['website'],
                'price' => 800
            ],
            'CraftCMS' => [
                'category' => $productCategories['website'],
                'price' => 500
            ],
            'ApiPlatform' => [
                'category' => $productCategories['api'],
                'price' => 50_000
            ],
            'Onderhoud' => [
                'category' => $productCategories['service'],
                'price' => 5_000
            ],
            'Email hosting' => [
                'category' => $productCategories['service'],
                'price' => 1_250
            ],
            'Webhosting' => [
                'category' => $productCategories['service'],
                'price' => 31_250
            ],
            'Demo' => [
                'category' => $productCategories['service'],
                'price' => 25
            ],
        ];

        foreach ($products as $name => $options) {
            $product = (new Product())
                ->setName($name)
                ->setProductCategory($options['category'])
                ->setPrice($options['price'])
            ;
            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductCategoryFixtures::class
        ];
    }
}
