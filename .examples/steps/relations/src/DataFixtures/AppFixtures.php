<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [];
        foreach (['Конференции', 'Досуг'] as $name) {
            $category = new Category()->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        $titles = ['Встреча PHP-сообщества', 'Вечер настольных игр'];
        $descriptions = ['Обсуждаем тестирование и разработку приложений.', 'Знакомимся с новыми играми.'];
        foreach ($titles as $index => $title) {
            $event = new Event()
                ->setTitle($title)
                ->setDescription($descriptions[$index])
                ->setStartsAt(new \DateTimeImmutable('+1 year +' . $index . ' day'));
            $event->setCategory($categories[$index]);

            $manager->persist($event);
        }
        $manager->flush();
    }
}
