<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use App\Entity\FIltre;

class StatService
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function fetchCountOfAll(): array
    {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM secteur_electrique) AS nb_secteur,
                (SELECT COUNT(*) FROM post_electrique) AS nb_post,
                (SELECT COUNT(*) FROM infrastructure WHERE type_infra = 1) AS nb_shop,
                (SELECT COUNT(*) FROM infrastructure WHERE type_infra = 2) AS nb_agence
        ";
        $results = $this->conn->fetchAllAssociative($sql);

        return $results[0];
    }

    public function getStatEffectifNivColumnAndView($niv) {
        if($niv == "region") {
            return [
                'columns' => '*', 
                'view' => 'v_effectif_rg'
            ];
        }
        return [
            'columns' => "$niv, '-' as nb_secteur, '-' as nb_post, SUM(nb_shop) AS nb_shop, SUM(nb_agence) AS nb_agence ", 
            'view' => 'v_effectif_infra'
        ];
    }

    public function fetchCountInfraGroupByLocation($niv, $motclee, $order, $inc): array
    {
         // Pour éviter les injections SQL
        $niv = addslashes($niv);
        $motclee = addslashes($motclee);
        $order = addslashes($order);
        $inc = addslashes($inc);
        
        $option = $this->getStatEffectifNivColumnAndView($niv);
        $sql = "
            SELECT 
                {$option['columns']}
            FROM {$option['view']}
            WHERE {$niv} LIKE '%{$motclee}%'
            GROUP BY {$niv}
            ORDER BY {$order} {$inc}
        ";

        // Exécutez la requête et obtenez les résultats sous forme associative
        $results = $this->conn->fetchAllAssociative($sql);

        return $results;
    }

    public function fetchEffectifByFilter1(Filtre $filtre): array
    {
        $niv = $filtre->getGroupby();
        $motclee = $filtre->getMotclee();
        $order = $filtre->getOrderby();
        $inc = $filtre->getOrderinc();
        
        $option = $this->getStatEffectifNivColumnAndView($niv);
        $sql = "
            SELECT 
                {$option['columns']}
            FROM {$option['view']}
            WHERE {$niv} LIKE '%{$motclee}%'
            GROUP BY {$niv}
            ORDER BY {$order} {$inc}
        ";

        // Exécutez la requête et obtenez les résultats sous forme associative
        $results = $this->conn->fetchAllAssociative($sql);

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

        $totalCountResult = $this->conn->fetchOne($countSql);
        $totalCount = (int) $totalCountResult;
        
        // Calculate the total number of pages
        $totalPages = ceil($totalCount / $limit);
        return $totalPages;
    }

    public function fetchEffectifByFilter(Filtre $filtre): array
    {
        $niv = $filtre->getGroupby();
        $motclee = $filtre->getMotclee();
        $order = $filtre->getOrderby();
        $inc = $filtre->getOrderinc();
        $page = $filtre->getPage();
        $limit = $filtre->getLimit();

        $option = $this->getStatEffectifNivColumnAndView($niv);
        
        // Calculate offset for pagination
        $offset = ($page - 1) * $limit;

        // Query to get the paginated results
        $sql = "
            SELECT 
                {$option['columns']}
            FROM {$option['view']}
            WHERE {$niv} LIKE '%{$motclee}%'
            GROUP BY {$niv}
            ORDER BY {$order} {$inc}
        ";

        $count = $this->countResult($sql, $limit);
        $sql_limit = "{$sql} LIMIT {$limit} OFFSET {$offset}";

        // Execute the query and get the results
        $results = $this->conn->fetchAllAssociative($sql_limit);

        return [
            'results' => $results,
            'totalPages' => $count,
            'currentPage' => $page
        ];
    }

    public function fetchStatCoupureByFilter(Filtre $filtre): array
    {
        $niv = $filtre->getGroupby();
        $motclee = $filtre->getMotclee();
        $order = $filtre->getOrderby();
        $inc = $filtre->getOrderinc();
        $page = $filtre->getPage();
        $limit = $filtre->getLimit();
        
        // Calculate offset for pagination
        $offset = ($page - 1) * $limit;

        // Query to get the paginated results
        $sql = "
            SELECT * FROM (
                SELECT 
                    niveau,
                    SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
                    COUNT(id_coupure) AS frequence_coupure,
                    (SELECT 
                            heure_coupure
                            FROM v_coupure_{$niv} AS vcniv_sub
                            WHERE vcniv_sub.niveau = vcniv.niveau
                            GROUP BY heure_coupure
                            ORDER BY COUNT(*) DESC
                            LIMIT 1) AS heure_coupure_frequent,
                    SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
                FROM v_coupure_{$niv} vcniv
                WHERE {$filtre->getDateFilter()}
                GROUP BY niveau
                ORDER BY niveau 
            ) sr
            WHERE niveau LIKE '%{$motclee}%'
            ORDER BY {$order} {$inc}
        ";

        //echo($sql);

        $count = $this->countResult($sql, $limit);
        $sql_limit = "{$sql} LIMIT {$limit} OFFSET {$offset}";

        // Execute the query and get the results
        $results = $this->conn->fetchAllAssociative($sql_limit);

        return [
            'results' => $results,
            'totalPages' => $count,
            'currentPage' => $page,
            // 'sql' => $sql
        ];
    }

    public function buildStatCoupureByNiveauQuery($niv_date, $relation, $condition):string {
        $sql = "
            SELECT 
                niveau,
                {$niv_date} AS niv_date,
                SEC_TO_TIME(SUM(TIME_TO_SEC(duree_coupure))) AS heure_total_coupure,
                COUNT(id_coupure) AS frequence_coupure,
                (SELECT 
                        heure_coupure
                        FROM {$relation} AS vcs_sub
                        WHERE vcs_sub.niveau = vcs.niveau
                        GROUP BY heure_coupure
                        ORDER BY COUNT(*) DESC
                        LIMIT 1) AS heure_coupure_frequent,
                SEC_TO_TIME(AVG(TIME_TO_SEC(duree_coupure))) AS duree_moyenne_coupure
            FROM {$relation} vcs
            WHERE {$condition}
            GROUP BY niveau, niv_date
            ORDER BY niv_date asc
        ";

        return $sql;
    }

    public function fetchStatCoupureByNiveau(Filtre $filtre): array
    {
        $niv = $filtre->getGroupby();
        $motclee = $filtre->getMotclee();
        $datevalue = $filtre->getDateValue();
        
        // Calculate offset for pagination
        $option = $filtre->getDateFilterOptions();

        // Query to get the paginated results
        $view = "v_coupure_{$niv}";
        $condition = "{$option['filterby']} = '{$datevalue}' AND niveau = '{$motclee}'";
        
        $sql_all = $this->buildStatCoupureByNiveauQuery($option['filterby'], $view, $condition); // Avoir le resultat d'ensemble pour le niv_date selectionnee
        $sql = $this->buildStatCoupureByNiveauQuery($option['filterof'], $view, $condition); // Avoir la list qui forme le resultat

        // echo($sql);

        // Execute the query and get the results
        $general = $this->conn->fetchAllAssociative($sql_all);
        $results = $this->conn->fetchAllAssociative($sql);

        return [
            'general' => $general,
            'results' => $results,
            // 'sql' => $sql
        ];
    }
}
