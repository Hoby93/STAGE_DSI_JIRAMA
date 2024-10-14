<?php

namespace App\Repository;

use App\Entity\TypeInfrastructure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeInfrastructure>
 *
 * @method TypeInfrastructure|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeInfrastructure|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeInfrastructure[]    findAll()
 * @method TypeInfrastructure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeInfrastructureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeInfrastructure::class);
    }

//    /**
//     * @return TypeInfrastructure[] Returns an array of TypeInfrastructure objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TypeInfrastructure
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
