<?php

namespace App\DataFixtures;

use App\Entity\Filter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            (new Filter())
                ->setName('Old things')
                ->setCriteria([['type' => 'date', 'op' => 'lt', 'value' => date('c')]])
        );
        $manager->persist(
            (new Filter())
                ->setName('Big things')
                ->setCriteria([['type' => 'amount', 'op' => 'gt', 'value' => '42']])
        );

        $manager->flush();
    }
}
