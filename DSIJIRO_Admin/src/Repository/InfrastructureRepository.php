<?php

namespace App\Repository;

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

    public function save($site): void
    {
        $query = "
            INSERT INTO infrastructure (ref_infra, type_infra, fkt_id, libelle, adresse, contact, descr, horaire, coord) 
            VALUES (:ref, :type, :fkt, :libelle , :adresse, :contact, :descr, :horaire, ST_GeomFromText(:point))
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $this->getEntityManager()->getConnection()->executeStatement($query, [
            'ref' => $site->getRefInfra(),
            'type' => $site->getTypeInfra(),
            'fkt' => $site->getFktId(),
            'libelle' => $site->getLibelle(),
            'adresse' => $site->getAdresse(),
            'contact' => $site->getContact(),
            'descr' => $site->getDescr(),
            'horaire' => $site->getHoraire(),
            'point' => $site->getCoordToPoint(),
        ]);
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

    public function findById(int $id_infra)
    {

        $sql = "
            select * from infrastructure
            where id_infra = {$id_infra}
        ";

        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\Infrastructure', 'i');

        $nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        // Requête principale pour les résultats paginés
        $result = $nativeQuery->getResult();

        return $result[0];
    }

    // Pour items marche, on trie les donnees (A-Z) lors de l insertion et celle du valeur a comparer
    public function findByMultiFilter(Filtre $filtre)
    {
        $page = $filtre->getPage();
        $limit = $filtre->getLimit();

        $offset = ($page - 1) * $limit;

        $sql = "
            select * from infrastructure
            where libelle like '%{$filtre->getMotclee()}%' or adresse like '%{$filtre->getMotClee()}%'
        ";

        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\Infrastructure', 'i');
        
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
