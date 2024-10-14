<?php

namespace App\Repository;

use App\Doctrine\PointType;
use App\Entity\Filtre;
use App\Entity\Infrastructure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Infrastructure>
 *
 * @method Infrastructure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Infrastructure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Infrastructure[]    findAll()
 * @method Infrastructure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfrastructureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Infrastructure::class);
    }

    // Pour items marche, on trie les donnees (A-Z) lors de l insertion et celle du valeur a comparer
    public function findNearestOfUserPosition($lat, $lng)
    {   
        $sql = "
            SELECT i.type_infra, libelle, i.coord
            FROM infrastructure i
            JOIN (
                SELECT type_infra, MIN(ST_Distance_Sphere(coord, ST_PointFromText('POINT({$lat} {$lng})'))) AS min_distance
                FROM infrastructure
                GROUP BY type_infra
            ) t ON i.type_infra = t.type_infra
            AND ST_Distance_Sphere(coord, ST_PointFromText('POINT({$lat} {$lng})')) = t.min_distance
        ";

        // Execute the query and get the results
        $results = $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        $ans = [];
        foreach ($results as $row) {
            $ans[$row['type_infra']] = PointType::convert($row['coord']);
        }

        return $ans;
    }

//    /**
//     * @return Infrastructure[] Returns an array of Infrastructure objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Infrastructure
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
