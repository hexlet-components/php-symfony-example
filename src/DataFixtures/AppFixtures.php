<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $categories = [];
        foreach (['Конференции', 'Досуг'] as $name) {
            $category = new Category()->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }
        $users = [];
        foreach (['alice', 'bob', 'admin'] as $name) {
            $user = new User()
                ->setEmail($name . '@example.test')
                ->setRoles($name === 'admin' ? ['ROLE_ADMIN'] : []);
            $user->setPassword($this->hasher->hashPassword($user, 'study-password'));
            $manager->persist($user);
            $users[] = $user;
        }

        $titles = ['Встреча PHP-сообщества', 'Вечер настольных игр'];
        $descriptions = ['Обсуждаем тестирование и разработку приложений.', 'Знакомимся с новыми играми.'];
        foreach ($titles as $index => $title) {
            $event = new Event()
                ->setTitle($title)
                ->setDescription($descriptions[$index])
                ->setStartsAt(new \DateTimeImmutable('+1 year +' . $index . ' day'));
            $event->setCategory($categories[$index]);
            $event->setOwner($users[$index]);
            $manager->persist($event);
        }
        $manager->flush();
    }
}
