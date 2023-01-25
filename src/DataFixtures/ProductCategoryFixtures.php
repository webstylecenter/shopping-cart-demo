<?php

namespace App\DataFixtures;

use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'website' => null,
            'api' => 5,
            'service' => 10,
        ];

        foreach ($categories as $category => $discount) {
            $serviceCategory = (new ProductCategory())
                ->setName($category)
                ->setDiscountPercentage($discount);
            $manager->persist($serviceCategory);
        }

        $manager->flush();
    }
}
