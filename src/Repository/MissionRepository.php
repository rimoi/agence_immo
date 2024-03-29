<?php

namespace App\Repository;

use App\Entity\Mission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\Join;

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
        ?string $prices,
        ?string $ip = null
    ): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.archived = :archived')
            ->andWhere('m.published = :published')
            ->setParameter('archived', false)
            ->setParameter('published', true)
            ->orderBy('m.id', 'DESC');

        if ($ip) {
            $qb ->addSelect('l')
                ->leftJoin('m.likes', 'l', JOIN::WITH, 'l.ip = :ip')
                ->setParameter('ip', $ip);
        }

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
