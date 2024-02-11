<?php

namespace App\Repository;

use App\Entity\Linking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Linking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Linking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Linking[]    findAll()
 * @method Linking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Linking::class);
    }

    // /**
    //  * @return Linking[] Returns an array of Linking objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Linking
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
