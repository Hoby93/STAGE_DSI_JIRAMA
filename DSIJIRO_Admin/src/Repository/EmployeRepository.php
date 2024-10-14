<?php

namespace App\Repository;

use App\Entity\Employe;
use App\Entity\Filtre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employe>
 *
 * @method Employe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employe[]    findAll()
 * @method Employe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    public function findByLog(string $email, string $mdp): ?Employe
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.email = :email')
            ->andWhere('e.mdp = :mdp')
            ->setParameter('email', $email)
            ->setParameter('mdp', $mdp)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countResult($sql, $limit) {
        $countSql = "
            SELECT 
                COUNT(*) as total 
            FROM (
                {$sql}
            ) as total_rows
        ";

        $totalCountResult = $this->getEntityManager()->getConnection()->fetchOne($countSql);
        $totalCount = (int) $totalCountResult;
        
        // Calculate the total number of pages
        $totalPages = ceil($totalCount / $limit);
        return $totalPages;
    }

    // Pour items marche, on trie les donnees (A-Z) lors de l insertion et celle du valeur a comparer
    public function findByMultiFilter(Filtre $filtre)
    {
        $page = $filtre->getPage();
        $limit = $filtre->getLimit();

        $offset = ($page - 1) * $limit;

        $sql = "
            select 
                * 
                from employe
                where nom like '%{$filtre->getMotclee()}%' or prenom like '%{$filtre->getMotClee()}%'
        ";

        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\Employe', 'po');
        
        $sql_limit = "{$sql} LIMIT {$limit} OFFSET {$offset}";

        $nativeQuery = $this->getEntityManager()->createNativeQuery($sql_limit, $rsm);

        // Requête principale pour les résultats paginés
        $results = $nativeQuery->getResult();

        // Calcul du nombre total de pages
        $totalPages = $this->countResult($sql, $limit);

        return [
            'results' => $results,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }

//    /**
//     * @return Employe[] Returns an array of Employe objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Employe
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
