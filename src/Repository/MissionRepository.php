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
        array $args,
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

        if ( $args['q'] ?? false ) {
            $qb->andWhere('m.title LIKE :term')
                ->setParameter('term', '%'.$args['q'].'%');
        }

        if ( $args['type'] ?? false ) {
            $qb
                ->join('m.tag', 't')
                ->andWhere('t.id = :tag_id')
                ->setParameter('tag_id', $args['type']);
        }

        if ( $args['priceMin'] ?? false ) {
            $qb->andWhere('m.price >= :priceMin')
                ->setParameter('priceMin', (int) $args['priceMin']);
        }
        if ( $args['priceMax'] ?? false ) {
            $qb->andWhere('m.price <= :priceMax')
                ->setParameter('priceMax', (int) $args['priceMax']);
        }

        if ( $args['district'] ?? false ) {
            $qb->andWhere('m.district = :district')
                ->setParameter('district', (int) $args['district']);
        }
        if ( $args['city'] ?? false ) {
            $qb->andWhere('m.city = :city')
                ->setParameter('city', (int) $args['city']);
        }

        return $qb;
    }
}
