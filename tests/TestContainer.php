<?php

namespace App\Tests;

use App\DataFixtures\ProductCategoryFixtures;
use App\DataFixtures\ProductFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class TestContainer extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = static::getContainer();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->initDatabase($kernel);
    }

    protected function initDatabase(KernelInterface $kernel): void
    {
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($metaData);

        $productCategoryFixtures = $this->container->get(ProductCategoryFixtures::class);
        $productCategoryFixtures->load($this->entityManager);

        $productFixtures = $this->container->get(ProductFixtures::class);
        $productFixtures->load($this->entityManager);
    }
}