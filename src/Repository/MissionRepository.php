<?php

namespace App\Repository;

use App\Entity\Mission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Mission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mission[]    findAll()
 * @method Mission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Mission::class);
    }

    public function getMissiosQueryBuilder(
        ?string $term,
        ?string $prices
    ): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.archived = :archived')
            ->setParameter('archived', false)
            ->orderBy('m.id', 'DESC');

        if ($term) {
            $qb->andWhere('m.title LIKE :term')
                ->setParameter('term', '%'.$term.'%');
        }

        if ($prices) {

            $prices = explode('-', $prices);

            $qb->andWhere('m.price >= :price1 AND m.price <= :price2')
                ->setParameter('price1', $prices[0])
                ->setParameter('price2', $prices[1])
            ;
        }

        return $qb;
    }

    // /**
    //  * @return Mission[] Returns an array of Mission objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mission
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
