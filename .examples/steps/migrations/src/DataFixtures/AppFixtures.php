<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $titles = ['Встреча PHP-сообщества', 'Вечер настольных игр'];
        $descriptions = ['Обсуждаем тестирование и разработку приложений.', 'Знакомимся с новыми играми.'];
        foreach ($titles as $index => $title) {
            $event = new Event()
                ->setTitle($title)
                ->setDescription($descriptions[$index])
                ->setStartsAt(new \DateTimeImmutable('+1 year +' . $index . ' day'));

            $manager->persist($event);
        }
        $manager->flush();
    }
}
