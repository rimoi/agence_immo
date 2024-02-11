<?php

namespace App\DataFixtures;

use App\Entity\Review;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ReviewFixture extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(100, 'main_reviews', function() {
            $review = new Review();
            $review->setContent(
                $this->faker->sentences(2, true)
            );

            $review->setNumberOfStar((int) random_int(1, 5));

            $review->setMission($this->getRandomReference('main_articles'));
            $review->setUser($this->getRandomReference('main_users'));

            return $review;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            MissionFixtures::class,
            UserFixture::class
        ];
    }
}
