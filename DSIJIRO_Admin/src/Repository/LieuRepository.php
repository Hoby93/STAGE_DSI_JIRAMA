<?php

namespace App\Repository;

use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lieu>
 *
 * @method Lieu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lieu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lieu[]    findAll()
 * @method Lieu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    public function insertMissingPlace(array $places, int $secteurId): void
    {
        // Start the dynamic SQL query construction
        $valuesQuery = '';
        $params = ['secteur_id' => $secteurId];
    
        foreach ($places as $index => $place) {
            $paramName = ":name{$index}";
    
            // Construct part of the SQL query
            $valuesQuery .= "SELECT :secteur_id AS secteur_id, {$paramName} AS nom UNION ALL ";
    
            // Add parameters to the array
            $params[$paramName] = $place;
        }
    
        // Remove the last "UNION ALL "
        $valuesQuery = rtrim($valuesQuery, ' UNION ALL ');
    
        // The final SQL query
        $query = "
            INSERT INTO lieu (secteur_id, nom)
            SELECT secteur_id, nom
            FROM ({$valuesQuery}) AS tmp
            WHERE nom NOT IN (
                SELECT nom FROM lieu WHERE secteur_id = :secteur_id
            )
        ";

        //echo($query);
    
        // Execute the query using a prepared statement
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute($params);
    }    

//    /**
//     * @return Lieu[] Returns an array of Lieu objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Lieu
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
