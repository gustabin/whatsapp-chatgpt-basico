<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Defines the sample data to load in the database when running the unit and
 * functional tests or while development.
 *
 * Execute this command to load the data:
 * bin/console doctrine:fixtures:load
 *
 * @codeCoverageIgnore
 */
final class TagFixtures extends Fixture
{
    public const MIN_TAGS = 50;
    public const MAX_TAGS = 2000;
    public const BATCH_SIZE = 100;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $amount = rand(self::MIN_TAGS, self::MAX_TAGS);
        $existing = [];

        for ($i = 0; $i < $amount; $i++) {
            $tag = new Tag();

            $tagName = null;
            if ($i % 18 === 0) {
                $tagName = $faker->firstName();
            } elseif ($i % 7 === 0) {
                $tagName = $faker->lastName();
            } elseif ($i % 5 === 0) {
                $tagName = $faker->city();
            } elseif ($i % 4 === 0) {
                $tagName = $faker->word();
            } elseif ($i % 3 === 0) {
                $tagName = $faker->streetName();
            } elseif ($i % 2 === 0) {
                $tagName = $faker->colorName();
            } else {
                $tagName = $faker->text(rand(5, 10));
            }

            if (\in_array(mb_strtolower($tagName), $existing, true)) {
                continue;
            }

            $existing[] = mb_strtolower($tagName);
            $tag->setName(mb_substr($tagName, 0, 100));

            $manager->persist($tag);

            if ($i % self::BATCH_SIZE === 0) {
                $manager->flush();
                $manager->clear();
            }
        }
        $manager->flush();
        $manager->clear();
    }
}
