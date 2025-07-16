<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Commentary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentary>
 */
class CommentaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentary::class);
    }

    /**
     * Retourne tous les commentaires validés liés à une formation NetPitch.
     *
     * @return Commentary[]
     */
    public function findValidatedCommentariesWithFormations(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.netPitchFormation', 'formation')
            ->leftJoin('c.user', 'u')
            ->addSelect('formation', 'u')
            ->andWhere('c.statutCommentary = :status')
            ->setParameter('status', 'validé')
            ->andWhere('c.netPitchFormation IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les commentaires validés liés à un événement archivé.
     *
     * @return Commentary[]
     */
    public function findValidatedCommentariesWithEvents(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.archivedEvent', 'event')
            ->leftJoin('c.user', 'u')
            ->addSelect('event', 'u')
            ->andWhere('c.statutCommentary = :status')
            ->setParameter('status', 'validé')
            ->andWhere('c.archivedEvent IS NOT NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les commentaires validés d’un utilisateur pour les formations.
     */
    public function findValidatedFormationCommentariesByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.netPitchFormation', 'formation')
            ->addSelect('formation')
            ->andWhere('c.user = :user')
            ->andWhere('c.statutCommentary = :status')
            ->andWhere('c.netPitchFormation IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', 'validé')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les commentaires validés d’un utilisateur pour les événements archivés.
     */
    public function findValidatedEventCommentariesByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.archivedEvent', 'archivedEvent')
            ->leftJoin('archivedEvent.event', 'event')
            ->addSelect('archivedEvent', 'event')
            ->andWhere('c.user = :user')
            ->andWhere('c.statutCommentary = :status')
            ->andWhere('c.archivedEvent IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', 'validé')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Commentary[] Returns an array of Commentary objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Commentary
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
