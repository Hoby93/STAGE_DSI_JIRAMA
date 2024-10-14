<?php

namespace App\Repository;

use App\Entity\Filtre;
use App\Entity\PostElectrique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostElectrique>
 *
 * @method PostElectrique|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostElectrique|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostElectrique[]    findAll()
 * @method PostElectrique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostElectriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostElectrique::class);
    }

    public function save(PostElectrique $new): void
    {
        // The final SQL query
        $query = "
            INSERT INTO post_electrique (ref_post, secteur_id)
                                        VALUE(:ref, :secteurid);
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $this->getEntityManager()->getConnection()->executeStatement($query, [
            "ref" => $new->getRefPost(),
            "secteurid" => $new->getSecteurId()
        ]);
    }

    public function update(PostElectrique $poste): void
    {
        // Requête SQL finale
        $query = "
            UPDATE post_electrique SET ref_post = :ref, secteur_id = :secteurid
            WHERE id_post = :idpost
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $this->getEntityManager()->getConnection()->executeStatement($query, [
            "ref" => $poste->getRefPost(),
            "secteurid" => $poste->getSecteurId(),
            "idpost" => $poste->getId()
        ]);
    }

    public function insertMissingPosts(string $refPostes, int $id_secteur): void
    {
        // Split the concatenated string into an array of individual references
        $refsArray = explode('-', $refPostes);

        // Start the dynamic SQL query construction
        $valuesQuery = '';
        $params = ['id_secteur' => $id_secteur];

        foreach ($refsArray as $index => $refPost) { // Creer une liste de ref_post a partie du chaine concatenee
            $paramName = ":ref{$index}";
            $valuesQuery .= "SELECT {$paramName} AS ref_post UNION ALL ";
            $params[$paramName] = $refPost;
        }

        // Supprimer le dernier "UNION ALL "
        $valuesQuery = rtrim($valuesQuery, ' UNION ALL ');

        // The final SQL query
        $query = "
            INSERT INTO post_electrique (ref_post, secteur_id)
            SELECT ref_post, :id_secteur FROM ({$valuesQuery}) AS tmp
            WHERE ref_post NOT IN (
                SELECT ref_post FROM post_electrique
            )
        ";

        // echo($query);

        // Execute the query using a prepared statement
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute($params);
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
            secteur_electrique.ref_secteur as secteur, post_electrique.* 
                from post_electrique
                join secteur_electrique on secteur_electrique.id_secteur = post_electrique.secteur_id
                where ref_secteur like '%{$filtre->getMotclee()}%' or ref_post like '%{$filtre->getMotClee()}%'
        ";

        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\PostElectrique', 'po');
        $rsm->addScalarResult('secteur', 'secteur'); // Mapping pour le ref_secteur du secteur
        
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
//     * @return PostElectrique[] Returns an array of PostElectrique objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PostElectrique
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
