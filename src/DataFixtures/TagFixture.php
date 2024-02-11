<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Common\Persistence\ObjectManager;

class TagFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_tags', function() {
            $tag = new Tag();
            $tag->setName($this->faker->realText(20));
            $tag->setContent($this->faker->realText(200));

            return $tag;
        });

        $manager->flush();
    }
}
