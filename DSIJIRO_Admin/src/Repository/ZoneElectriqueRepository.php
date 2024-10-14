<?php

namespace App\Repository;

use App\Entity\Filtre;
use App\Entity\ZoneElectrique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @extends ServiceEntityRepository<ZoneElectrique>
 *
 * @method ZoneElectrique|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZoneElectrique|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZoneElectrique[]    findAll()
 * @method ZoneElectrique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZoneElectriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZoneElectrique::class);
    }

    public function getLastZoneElectriqueId(): ?int
    {
        $sql = "SELECT MAX(id_zone) AS last_id FROM zone_electrique";
        
        // Exécuter la requête et obtenir le résultat
        $result = $this->getEntityManager()->getConnection()->fetchAssociative($sql);
        
        // Retourner l'ID ou null si aucune valeur trouvée
        return $result['last_id'] ?? null;
    }

    public function save($zone, $coords_wkt): void
    {
        $query = "
            INSERT INTO zone_electrique (ref_zone, ref_secteur, specificite, lieux, postes, degree, coord) 
            VALUES (:ref_zone, :ref_secteur, :specificite, :lieux, :postes, :degree , ST_GeomFromText(:polygon))
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $result = $this->getEntityManager()->getConnection()->executeStatement($query, [
            'ref_zone' => $zone->getRefZone(),
            'ref_secteur' => $zone->getRefSecteur(),
            'specificite' => $zone->getSpecificite(),
            'lieux' => $zone->getLieux(),
            'postes' => $zone->getPostes(),
            'degree' => $zone->getDegree(),
            'polygon' => $coords_wkt,
        ]);
    }

    public function update($zone, $coords_wkt): void
    {
        $query = "
            UPDATE zone_electrique 
            SET ref_zone = :ref_zone, 
                ref_secteur = :ref_secteur, 
                specificite = :specificite, 
                lieux = :lieux, 
                postes = :postes, 
                degree = :degree, 
                coord = ST_GeomFromText(:polygon)
            WHERE id_zone = :id_zone
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $result = $this->getEntityManager()->getConnection()->executeStatement($query, [
            'ref_zone' => $zone->getRefZone(),
            'ref_secteur' => $zone->getRefSecteur(),
            'specificite' => $zone->getSpecificite(),
            'lieux' => $zone->getLieux(),
            'postes' => $zone->getPostes(),
            'degree' => $zone->getDegree(),
            'polygon' => $coords_wkt,
            'id_zone' => $zone->getId(),
        ]);
    }


    public function saveMultipleZoneCoupee(int $idcoupure, string $idzones_str): void
    {
        // Split the concatenated string into an array of individual references
        $idzones = explode(',', $idzones_str);

        // Prepare the values query
        $valuesQuery = implode(', ', array_fill(0, count($idzones), '(?, ?)'));
        $params = [];

        // Create a subquery with all the values
        foreach ($idzones as $index => $id) {
            $params[] = $idcoupure;
            $params[] = $id;
        }

        // Construct the main query
        $query = "
            INSERT INTO coupure_zone (coupure_id, zone_id)
            VALUES {$valuesQuery}
        ";

        // Execute the query using a prepared statement
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute($params);
    }


    public function saveZoneCoupee(int $idcoupure, int $idzone): void
    {
        // The final SQL query
        $query = "
            INSERT INTO coupure_zone (coupure_id, zone_id)
            VALUES (:coupure_id, :zone_id)
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $this->getEntityManager()->getConnection()->executeStatement($query, [
            'coupure_id' => $idcoupure,
            'zone_id' => $idzone
        ]);
    }

    public function resetZonePostAndPlace(int $idzone): void
    {
        // The final SQL query
        $query = "
            DELETE FROM zone_post WHERE zone_id = :zone_id;
            DELETE FROM zone_lieu WHERE zone_id = :zone_id;
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $this->getEntityManager()->getConnection()->executeStatement($query, [
            'zone_id' => $idzone
        ]);
    }

    public function saveZonePost(string $refPostes, int $zoneId): void
    {
        // Start the dynamic SQL query construction
        $valuesQuery = '';
    
            // Construire la requête pour insérer dans `zone_lieu`
        $valuesQuery = '';
        $params = [];

        // Split the concatenated string into an array of individual references
        $refsArray = explode('-', $refPostes);

        // Start the dynamic SQL query construction
        $valuesQuery = '';

        foreach ($refsArray as $index => $refPost) { // Creer une liste de ref_post a partie du chaine concatenee
            $refId = ":ref{$index}";
            $valuesQuery .= "SELECT {$refId} AS ref_post UNION ALL ";
            $params[$refId] = $refPost;
        }

        // Supprimer le dernier "UNION ALL "
        $valuesQuery = rtrim($valuesQuery, ' UNION ALL ');

        // Requête SQL finale pour insérer dans `zone_lieu`
        $query = "
            INSERT INTO zone_post (zone_id, post_id)
            SELECT :zone_id, p.id_post
            FROM post_electrique p
            INNER JOIN ({$valuesQuery}) AS tmp ON p.ref_post = tmp.ref_post
            WHERE NOT EXISTS (
                SELECT 1 FROM zone_post zp
                WHERE zp.zone_id = :zone_id AND zp.post_id = p.id_post
            )
        ";

        // Ajouter le paramètre pour `zone_id`
        $params[':zone_id'] = $zoneId;
    
        // Execute the query using a prepared statement
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute($params);
    }  

    public function saveZonePlace(array $places, int $zoneId): void
    {
        // Start the dynamic SQL query construction
        $valuesQuery = '';
    
            // Construire la requête pour insérer dans `zone_lieu`
        $valuesQuery = '';
        $params = [];

        foreach ($places as $index => $place) {
            $paramId = ":id{$index}";

            // Ajouter chaque place dans la sous-requête
            $valuesQuery .= "SELECT {$paramId} AS place_name UNION ALL ";
            
            // Ajouter les paramètres pour chaque place
            $params[$paramId] = $place;
        }

        // Supprimer le dernier "UNION ALL "
        $valuesQuery = rtrim($valuesQuery, ' UNION ALL ');

        // Requête SQL finale pour insérer dans `zone_lieu`
        $query = "
            INSERT INTO zone_lieu (zone_id, lieu_id)
            SELECT :zone_id, l.id_lieu
            FROM lieu l
            INNER JOIN ({$valuesQuery}) AS tmp ON l.nom = tmp.place_name
            WHERE NOT EXISTS (
                SELECT 1 FROM zone_lieu zl
                WHERE zl.zone_id = :zone_id AND zl.lieu_id = l.id_lieu
            )
        ";

        // Ajouter le paramètre pour `zone_id`
        $params[':zone_id'] = $zoneId;
    
        // Execute the query using a prepared statement
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute($params);
    }  
    
    // Pour items marche, on trie les donnees (A-Z) lors de l insertion et celle du valeur a comparer
    public function findById(string $id)
    {
        $sql = "
            select * from zone_electrique 
            where id_zone = '{$id}'
        ";

        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\ZoneElectrique', 'z');

        // Requête principale pour le résultat
        $result = $this->getEntityManager()->createNativeQuery($sql, $rsm)->getResult();

        return $result[0];
    }

    public function getAllIntersect($wkt_coord)
    {
        // Définissez votre requête SQL
        $sql = "SELECT z.* FROM zone_electrique z  WHERE ST_Intersects(ST_GeomFromText(:polygon), coord)";
    
        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());

        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\ZoneElectrique', 'z');

        // Préparez la requête native
        $nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $nativeQuery->setParameter('polygon', $wkt_coord);

        // Exécutez la requête et obtenez le résultat
        $results = $nativeQuery->getResult();

        // Retournez le résultat
        return $results;
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
    public function findByMultiFilter(Filtre $filtre, string $ref, string $items)
    {
        $page = $filtre->getPage();
        $limit = $filtre->getLimit();

        $offset = ($page - 1) * $limit;

        $sql = "
            select * from zone_electrique 
            where ref_zone like '%{$ref}%' or lieux like '%{$items}%' or postes like '%{$items}%'
        ";

        // Créez un ResultSetMapping
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        // Ajoutez le mapping pour l'entité ZoneVente
        $rsm->addRootEntityFromClassMetadata('App\Entity\ZoneElectrique', 'z');
        
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


    // Pour que cela marche, on trie les donnees (A-Z) lors de l insertion et celle du valeur a comparer
    public function findByPostesLike(string $value)
    {
        return $this->createQueryBuilder('z')
            ->where('z.postes LIKE :value')
            ->setParameter('value', '%' . $value . '%')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return ZoneElectrique[] Returns an array of ZoneElectrique objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('z.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ZoneElectrique
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
