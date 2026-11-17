<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Event> */
final class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /** @return list<Event> */
    public function findUpcoming(string $query = '', int $limit = 20): array
    {
        $builder = $this
            ->createQueryBuilder('event')
            ->andWhere('event.startsAt >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('event.startsAt', 'ASC')
            ->addOrderBy('event.id', 'ASC')
            ->setMaxResults($limit);
        if ($query !== '') {
            $builder->andWhere('event.title LIKE :query')->setParameter('query', '%' . $query . '%');
        }
        return $builder->getQuery()->getResult();
    }
}
