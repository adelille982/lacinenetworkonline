<?php

namespace App\Repository;

use App\Entity\ArchivedEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArchivedEvent>
 */
class ArchivedEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchivedEvent::class);
    }

    public function findLastArchivedEventBeforeNow(): ?ArchivedEvent
    {
        return $this->createQueryBuilder('ae')
            ->join('ae.event', 'e')
            ->addSelect('e')
            ->where('e.dateEvent < :now')
            ->andWhere('ae.draft = false')
            ->orderBy('e.dateEvent', 'DESC')
            ->setParameter('now', new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findArchivedEventsWithValidBackToImage(): array
    {
        $results = $this->createQueryBuilder('ae')
            ->join('ae.event', 'e')
            ->addSelect('e')
            ->leftJoin('ae.backToImage', 'bti')
            ->addSelect('bti')
            ->leftJoin('bti.imageBackToImages', 'img')
            ->addSelect('img')
            ->where('ae.draft = false')
            ->andWhere('e.dateEvent < :now')
            ->andWhere('bti.textBackToImage IS NOT NULL')
            ->orderBy('e.dateEvent', 'DESC')
            ->setParameter('now', new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))
            ->getQuery()
            ->getResult();

        // 💡 Filtrer les résultats côté PHP sur le nombre d'images
        return array_filter($results, function ($archived) {
            return count($archived->getBackToImage()?->getImageBackToImages() ?? []) >= 2;
        });
    }

    //    /**
    //     * @return ArchivedEvent[] Returns an array of ArchivedEvent objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ArchivedEvent
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
