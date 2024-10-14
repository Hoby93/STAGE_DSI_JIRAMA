<?php

namespace App\Repository;

use App\Entity\Filtre;
use App\Entity\SecteurElectrique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @extends ServiceEntityRepository<SecteurElectrique>
 *
 * @method SecteurElectrique|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecteurElectrique|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecteurElectrique[]    findAll()
 * @method SecteurElectrique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecteurElectriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecteurElectrique::class);
    }

    public function save(SecteurElectrique $new): void
    {
        // The final SQL query
        $query = "
            INSERT INTO secteur_electrique (region_id, ref_secteur)
                                        VALUE(:idregion, :refsecteur);
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $this->getEntityManager()->getConnection()->executeStatement($query, [
            "idregion" => $new->getRegionId(),
            "refsecteur" => $new->getRefSecteur()
        ]);
    }

    public function update(SecteurElectrique $secteur): void
    {
        // Requête SQL finale
        $query = "
            UPDATE secteur_electrique SET region_id = :regionid, ref_secteur = :refsecteur
            WHERE id_secteur = :idsecteur
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $this->getEntityManager()->getConnection()->executeStatement($query, [
            "regionid" => $secteur->getRegionId(),
            "refsecteur" => $secteur->getRefSecteur(),
            "idsecteur" => $secteur->getId()
        ]);
    }

    public function insertMissingSecteur(string $refSecteur): void
    {
        // The final SQL query
        $query = "
            INSERT INTO secteur_electrique (ref_secteur)
            SELECT :ref_secteur
            WHERE NOT EXISTS (
                SELECT 1 FROM secteur_electrique WHERE ref_secteur = :ref_secteur
            )
        ";

        // Execute the query using a prepared statement
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute(['ref_secteur' => $refSecteur]);
    }

    public function findIdByRefSecteur($ref): ?int
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.id_secteur')
            ->where('s.refSecteur = :ref')
            ->setParameter('ref', $ref)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? (int) $result['id_secteur'] : null;
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
                region.libelle as region, secteur_electrique.* 
                from secteur_electrique
                join region on region.id_region = secteur_electrique.region_id
                where ref_secteur like '%{$filtre->getMotclee()}%' or libelle like '%{$filtre->getMotClee()}%'
        ";

        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\SecteurElectrique', 'i');
        $rsm->addScalarResult('region', 'region'); // Mapping pour le libelle de la région
        
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
//     * @return SecteurElectrique[] Returns an array of SecteurElectrique objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SecteurElectrique
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
