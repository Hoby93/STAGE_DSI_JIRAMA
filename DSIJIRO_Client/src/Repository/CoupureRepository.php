<?php

namespace App\Repository;

use App\Entity\Coupure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Coupure>
 *
 * @method Coupure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coupure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coupure[]    findAll()
 * @method Coupure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoupureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coupure::class);
    }

    public function getLastCoupureId(): ?int
    {
        $sql = "SELECT MAX(id_coupure) AS last_id FROM coupure";
        
        // Exécuter la requête et obtenir le résultat
        $result = $this->getEntityManager()->getConnection()->fetchAssociative($sql);
        
        // Retourner l'ID ou null si aucune valeur trouvée
        return $result['last_id'] ?? null;
    }

//    /**
//     * @return Coupure[] Returns an array of Coupure objects
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

//    public function findOneBySomeField($value): ?Coupure
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
